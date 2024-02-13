<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'firstname' => $this->faker->firstName(),
            'lastname'  =>$this ->faker->LastName(),
            'contact' => $this->faker->phoneNumber(),
            'ninno'=> $this->faker->unique()->randomNumber(),
            'dob'=> $this->faker->date($format= 'Y-m-d', $max ='now'),
            'joined'=> $this->faker->date($format= 'Y-m-d', $max ='now'), 
        ];
    }
}
