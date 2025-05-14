<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'subject'       => $this->faker->sentence(),
            'description'   => $this->faker->paragraph(),
            'category'      => $this->faker->randomElement(['technical', 'billing', 'general']),
            'priority'      => $this->faker->randomElement(['low', 'medium', 'high']),
            'status'        => $this->faker->randomElement(['open', 'in_progress', 'resolved', 'closed']),
        ];
    }
}
