<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RequestUpdateNotification;
use App\Services\Notifications\MessageTemplates;
use App\Support\Msg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Admin-editable notification texts (2026-10-17). */
class NotificationTextsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_message_is_well_formed(): void
    {
        $all = MessageTemplates::all();
        $this->assertGreaterThan(80, count($all));
        foreach ($all as $key => $message) {
            $this->assertArrayHasKey($message['group'], MessageTemplates::GROUPS, $key);
            $this->assertNotSame('', trim($message['label']), $key);
            $this->assertSame([], MessageTemplates::unknownPlaceholders($key, $message['text']), $key . ' uses a placeholder it does not declare');
        }
    }

    public function test_the_admin_rewrites_a_text_and_new_notifications_use_it(): void
    {
        $admin = User::factory()->admin()->create();
        $default = Msg::t('requests.offer_selected', ['number' => 'REQ-1001']);

        $this->actingAs($admin)->get(route('admin.notification-texts.index'))->assertOk()->assertSee('نصوص الإشعارات')->assertSee('{number}');
        $this->actingAs($admin)->post(route('admin.notification-texts.save'), ['texts' => ['requests.offer_selected' => 'مبروك! اختير عرضك {price}']])
            ->assertSessionHasErrors();
        $this->actingAs($admin)->post(route('admin.notification-texts.save'), ['texts' => ['requests.offer_selected' => 'مبروك! اختير عرضك على {number}، ابدأ الآن']])
            ->assertSessionHas('success');

        $this->assertSame('مبروك! اختير عرضك على REQ-1001، ابدأ الآن', Msg::t('requests.offer_selected', ['number' => 'REQ-1001']));
        $this->actingAs($admin)->get(route('admin.notification-texts.index'))->assertSee('معدّل');

        // Emptying it brings the original text back.
        $this->actingAs($admin)->post(route('admin.notification-texts.save'), ['texts' => ['requests.offer_selected' => '']]);
        $this->assertSame($default, Msg::t('requests.offer_selected', ['number' => 'REQ-1001']));
    }

    public function test_values_written_by_users_are_kept_as_they_are(): void
    {
        // A missing placeholder disappears; braces inside a user's note stay.
        $text = MessageTemplates::render('disputes.opened', ['number' => 'CNT-1001', 'reason' => 'سبب {مهم}  جدًا']);
        $this->assertStringContainsString('CNT-1001', $text);
        $this->assertStringContainsString('سبب {مهم}  جدًا', $text);
        $this->assertStringNotContainsString('{days}', MessageTemplates::render('disputes.opened', ['number' => 'CNT-1001']));
    }

    public function test_a_content_editor_may_edit_texts_and_an_analyst_may_not(): void
    {
        $editor = User::factory()->admin()->create(['admin_role' => 'content']);
        $analyst = User::factory()->admin()->create(['admin_role' => 'analyst']);
        $this->actingAs($editor)->post(route('admin.notification-texts.save'), ['texts' => ['requests.work_delivered' => 'تم التسليم {number}']])->assertSessionHas('success');
        $this->actingAs($analyst)->post(route('admin.notification-texts.save'), ['texts' => ['requests.work_delivered' => 'x']])->assertForbidden();
        (User::factory()->create())->notify(new RequestUpdateNotification(Msg::t('requests.work_delivered', ['number' => 'REQ-7']), '/x', 'request_updates'));
        $this->assertDatabaseHas('notifications', ['data->text' => 'تم التسليم REQ-7']);
    }
}
