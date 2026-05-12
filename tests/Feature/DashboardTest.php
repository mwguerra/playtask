<?php

use App\Filament\Admin\Widgets\AdminActivityChart;
use App\Filament\Admin\Widgets\AdminStats;
use App\Filament\Admin\Widgets\AdminTagsChart;
use App\Filament\Superadmin\Widgets\SuperadminStats;
use App\Models\BetaSignup;
use App\Models\TodoItem;
use App\Models\TodoList;
use App\Models\User;

function invokeProtected(object $instance, string $method): mixed
{
    $reflection = new ReflectionMethod($instance, $method);
    $reflection->setAccessible(true);

    return $reflection->invoke($instance);
}

test('admin dashboard route is reachable for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertOk();
});

test('admin stats widget reflects the authenticated user state', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->public()->create();
    TodoItem::factory(3)->for($list)->create();
    TodoItem::factory()->for($list)->completed()->create();

    $this->actingAs($user);

    $stats = invokeProtected(new AdminStats, 'getStats');

    expect($stats[0]->getValue())->toBe(1)
        ->and($stats[1]->getValue())->toBe(3)
        ->and($stats[2]->getValue())->toBe('25%');
});

test('superadmin dashboard is forbidden to regular users and reachable to superadmins', function () {
    $user = User::factory()->create();
    $admin = User::factory()->superadmin()->create();

    $this->actingAs($user)->get('/superadmin')->assertForbidden();
    $this->actingAs($admin)->get('/superadmin')->assertOk();
});

test('superadmin stats widget reflects global state', function () {
    User::factory()->count(2)->create();
    BetaSignup::factory()->count(5)->create();
    TodoList::factory()->public()->create();

    $stats = invokeProtected(new SuperadminStats, 'getStats');

    expect($stats[0]->getValue())->toBeGreaterThanOrEqual(3)
        ->and($stats[1]->getValue())->toBe(5)
        ->and($stats[2]->getValue())->toBeGreaterThanOrEqual(1);
});

test('admin tags chart aggregates tags from the authenticated users items', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();

    TodoItem::factory()->for($list)->create(['tags' => ['backend', 'urgent']]);
    TodoItem::factory()->for($list)->create(['tags' => ['backend', 'frontend']]);
    TodoItem::factory()->for($list)->create(['tags' => ['backend']]);

    $this->actingAs($user);

    $data = invokeProtected(new AdminTagsChart, 'getData');

    expect($data['labels'])->toContain('#backend')
        ->and(count($data['datasets'][0]['data']))->toBe(count($data['labels']));
});

test('admin activity chart returns 14 day series for created and completed', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();
    TodoItem::factory()->for($list)->completed()->create();
    TodoItem::factory(2)->for($list)->create();

    $this->actingAs($user);

    $data = invokeProtected(new AdminActivityChart, 'getData');

    expect($data['labels'])->toHaveCount(14)
        ->and($data['datasets'])->toHaveCount(2)
        ->and($data['datasets'][0]['label'])->toBe('Criados')
        ->and($data['datasets'][1]['label'])->toBe('Concluídos');
});

test('beta signups resource exists only in superadmin panel', function () {
    $admin = User::factory()->superadmin()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/beta-signups')
        ->assertNotFound();

    $this->actingAs($admin)
        ->get('/superadmin/beta-signups')
        ->assertOk();
});
