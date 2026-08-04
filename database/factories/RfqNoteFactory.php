<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RfqNote;
use App\Models\RfqRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RfqNote>
 */
class RfqNoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rfq_request_id' => RfqRequest::factory(),
            'user_id' => User::factory(),
            'note' => fake()->sentence(),
        ];
    }
}
