<?php

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

test('reverb connection is registered in broadcasting config', function () {
    expect(config('broadcasting.connections.reverb.driver'))->toBe('reverb')
        ->and(config('reverb.default'))->toBe('reverb');
});

test('filament panels are configured to use reverb echo', function () {
    expect(config('filament.broadcasting.echo'))->toBeArray()
        ->and(config('filament.broadcasting.echo.broadcaster'))->toBe('reverb');
});

test('broadcasting auth endpoint is registered', function () {
    expect(collect(Route::getRoutes())->contains(
        fn ($route) => $route->uri() === 'broadcasting/auth'
    ))->toBeTrue();
});

test('echo client is bundled into the public js entrypoint', function () {
    expect(File::get(resource_path('js/app.js')))->toContain('./echo');
    expect(File::get(resource_path('js/echo.js')))
        ->toContain('laravel-echo')
        ->and(File::get(resource_path('js/echo.js')))->toContain('reverb');
});

test('env example exposes required reverb variables', function () {
    $example = File::get(base_path('.env.example'));

    expect($example)
        ->toContain('BROADCAST_CONNECTION=reverb')
        ->toContain('REVERB_APP_ID')
        ->toContain('REVERB_APP_KEY')
        ->toContain('REVERB_APP_SECRET')
        ->toContain('REVERB_HOST')
        ->toContain('REVERB_PORT')
        ->toContain('VITE_REVERB_APP_KEY')
        ->toContain('VITE_REVERB_HOST')
        ->toContain('VITE_REVERB_PORT');
});

test('user lists channel authorizes the owner and denies others', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $broadcaster = Broadcast::getFacadeRoot()->driver('log');
    $reflection = new ReflectionClass($broadcaster);
    $channelsProperty = $reflection->getParentClass()->getProperty('channels');
    $channelsProperty->setAccessible(true);
    $channels = $channelsProperty->getValue($broadcaster);

    $callback = $channels['App.Models.User.{id}.lists'] ?? null;
    expect($callback)->not->toBeNull();

    expect($callback($owner, $owner->id))->toBeTrue()
        ->and($callback($other, $owner->id))->toBeFalse();
});

test('todo-list private channel authorizes only the owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $list = TodoList::factory()->for($owner)->create();

    $broadcaster = Broadcast::getFacadeRoot()->driver('log');
    $reflection = new ReflectionClass($broadcaster);
    $channelsProperty = $reflection->getParentClass()->getProperty('channels');
    $channelsProperty->setAccessible(true);
    $channels = $channelsProperty->getValue($broadcaster);

    $callback = $channels['todo-list.{listId}'] ?? null;
    expect($callback)->not->toBeNull();

    expect($callback($owner, $list->id))->toBeTrue()
        ->and($callback($other, $list->id))->toBeFalse();
});
