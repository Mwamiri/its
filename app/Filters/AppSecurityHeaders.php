<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AppSecurityHeaders implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null) {}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // HSTS — tell browsers to use HTTPS for the next year (only when already on HTTPS)
        if ($request->isSecure()) {
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Block the page being embedded in any frame or iframe (stronger than X-Frame-Options)
        $response->setHeader('Content-Security-Policy', "frame-ancestors 'none'");

        // Restrict access to sensitive browser APIs this app never needs
        $response->setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), bluetooth=(), interest-cohort=()');

        // Prevent MIME type sniffing — belt-and-suspenders alongside X-Content-Type-Options
        $response->setHeader('Cross-Origin-Resource-Policy', 'same-site');

        // Isolate this window from other browsing contexts (reduces XS-leak surface)
        $response->setHeader('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        return $response;
    }
}
