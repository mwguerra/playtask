<?php

use App\Enums\Complexity;
use App\Enums\Estimate;
use App\Models\BetaSignup;
use App\Models\TodoItem;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Hash;

test('TodoItem casts complexity and estimate to enums', function () {
    $item = TodoItem::factory()->create([
        'complexity' => 'high',
        'estimate' => 'weeks',
    ]);

    expect($item->complexity)->toBe(Complexity::High)
        ->and($item->estimate)->toBe(Estimate::Weeks);
});

test('TodoItem tags are stored as JSON array', function () {
    $item = TodoItem::factory()->create([
        'tags' => ['backend', 'urgent'],
    ]);

    expect($item->fresh()->tags)->toBe(['backend', 'urgent']);
});

test('TodoList password is hashed on save', function () {
    $list = TodoList::factory()->withPassword('my-plain-password')->create();

    expect(Hash::check('my-plain-password', $list->password))->toBeTrue()
        ->and($list->password)->not->toBe('my-plain-password');
});

test('TodoList belongs to a user', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();

    expect($list->user)->toBeInstanceOf(User::class)
        ->and($list->user->id)->toBe($user->id);
});

test('TodoList items are ordered by created_at ascending', function () {
    $list = TodoList::factory()->create();

    $old = TodoItem::factory()->for($list)->create(['created_at' => now()->subDay()]);
    $new = TodoItem::factory()->for($list)->create(['created_at' => now()]);

    expect($list->items->pluck('id')->all())->toBe([$old->id, $new->id]);
});

test('BetaSignup unique email constraint', function () {
    BetaSignup::factory()->create(['email' => 'dup@playtask.test']);

    expect(fn () => BetaSignup::factory()->create(['email' => 'dup@playtask.test']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('TodoList slug is unique globally', function () {
    TodoList::factory()->create(['slug' => 'slug-unico']);

    expect(fn () => TodoList::factory()->create(['slug' => 'slug-unico']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('Complexity enum has the three required cases', function () {
    expect(Complexity::cases())->toHaveCount(3);
});

test('Estimate enum has only hours/days/weeks without numbers', function () {
    expect(collect(Estimate::cases())->pluck('value')->all())
        ->toBe(['hours', 'days', 'weeks']);
});

test('isCompleted reflects the completed_at timestamp', function () {
    $item = TodoItem::factory()->create(['completed_at' => null]);
    expect($item->isCompleted())->toBeFalse();

    $item->update(['completed_at' => now()]);
    expect($item->fresh()->isCompleted())->toBeTrue();
});
