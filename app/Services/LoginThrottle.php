<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;

/**
 * Limitation du nombre de tentatives de connexion (OWASP A07 — force brute).
 *
 * L'état est conservé dans la session du visiteur : aucune table supplémentaire
 * n'est nécessaire et l'attaquant ne peut pas contourner le verrou en changeant
 * d'identifiant de session... puisque le compteur y est attaché. En production,
 * il faudrait également une limite par adresse IP (reverse-proxy + cache).
 */
final class LoginThrottle
{
    private const KEY          = 'security.login';
    public const MAX_ATTEMPTS  = 5;
    public const WINDOW_SECONDS = 900; // 15 min
    public const LOCK_SECONDS   = 900; // 15 min

    public function __construct(private readonly Session $session)
    {
    }

    /** @return array{count:int, first_at:int, locked_until:int} */
    private function &state(): array
    {
        $state = $this->session->get(self::KEY);
        if (!is_array($state)) {
            $state = ['count' => 0, 'first_at' => time(), 'locked_until' => 0];
        }

        $count = (int) ($state['count'] ?? 0);
        $first = (int) ($state['first_at'] ?? time());
        $locked = (int) ($state['locked_until'] ?? 0);

        // Fenêtre glissante : au-delà de 15 min, on repart de zéro.
        if ($first > 0 && time() - $first > self::WINDOW_SECONDS) {
            $count = 0;
            $first = time();
            $locked = 0;
        }
        if ($locked > 0 && $locked <= time()) {
            $count = 0;
            $first = time();
            $locked = 0;
        }

        $state = ['count' => $count, 'first_at' => $first, 'locked_until' => $locked];
        $this->session->set(self::KEY, $state);

        return $state;
    }

    public function isLocked(): bool
    {
        return $this->state()['locked_until'] > time();
    }

    public function attempts(): int
    {
        return $this->state()['count'];
    }

    public function remaining(): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attempts());
    }

    public function retryAfter(): int
    {
        return max(0, $this->state()['locked_until'] - time());
    }

    public function recordFailure(): void
    {
        $state = $this->state();
        $state['count']++;
        if ($state['count'] >= self::MAX_ATTEMPTS) {
            $state['locked_until'] = time() + self::LOCK_SECONDS;
        }
        $this->session->set(self::KEY, $state);
    }

    public function reset(): void
    {
        $this->session->forget(self::KEY);
    }
}
