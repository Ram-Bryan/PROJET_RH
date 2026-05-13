<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->has('user_id')) {
            return redirect()->to('/');
        }

        if (!$arguments) {
            return;
        }

        $requiredRole = $arguments[0];
        $userRole = $session->get('role');

        $allowed = match ($requiredRole) {
            'employe' => in_array($userRole, ['employe', 'rh', 'admin'], true),
            'rh' => in_array($userRole, ['rh', 'admin'], true),
            'admin' => $userRole === 'admin',
            default => false,
        };

        if (!$allowed) {
            return redirect()->to('/');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
