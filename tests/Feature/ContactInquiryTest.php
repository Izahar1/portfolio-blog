<?php

use App\Models\Inquiry;
use App\Notifications\NewInquiryNotification;
use Illuminate\Support\Facades\Notification;

it('stores a valid contact inquiry without the sender ip address', function () {
    Notification::fake();

    $response = $this->post(route('contact.store'), [
        'name' => 'Casey Example',
        'email' => 'casey@example.test',
        'subject' => 'Project question',
        'message' => 'I would like to discuss a project with you.',
        'website' => '',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('message', 'Thank you. Your message has been sent.');
    $this->assertDatabaseHas('inquiries', [
        'email' => 'casey@example.test',
        'status' => 'new',
        'ip_hash' => hash_hmac('sha256', '127.0.0.1', (string) config('app.key')),
    ]);
    Notification::assertSentOnDemand(NewInquiryNotification::class);
});

it('rejects contact messages with an invalid email', function () {
    $response = $this->from(route('contact.index'))->post(route('contact.store'), [
        'name' => 'Casey Example',
        'email' => 'not-an-email',
        'message' => 'I would like to discuss a project with you.',
        'website' => '',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Inquiry::query()->count())->toBe(0);
});
