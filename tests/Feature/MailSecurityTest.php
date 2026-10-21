<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailSecurityTest extends TestCase
{
    public function test_smtp_requires_tls_and_does_not_disable_certificate_checks(): void
    {
        $this->assertTrue(config('mail.mailers.smtp.verify_peer'));
        config(['mail.mailers.smtp.url' => null]);
        $transport = Mail::mailer('smtp')->getSymfonyTransport();

        $this->assertTrue($transport->isTlsRequired());
        $options = $transport->getStream()->getStreamOptions();
        $this->assertNotSame(false, $options['ssl']['verify_peer'] ?? true);
        $this->assertNotSame(false, $options['ssl']['verify_peer_name'] ?? true);
        $this->assertSame(15.0, $transport->getStream()->getTimeout());
    }
}
