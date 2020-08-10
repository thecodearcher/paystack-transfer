<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use WithFaker;
    protected $baseurl = 'api/auth';

    /**
     * Test case for register route.
     *
     * @return void
     */
    public function testRegisterUser()
    {

        $data = factory(User::class)->raw([
            'password' => $this->faker->password,
        ]);

        $response = $this->postJson("$this->baseurl/signup", $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "token",
                "user",
            ],
            "status",
            "message",
        ]);
    }

    public function testRegisterUserWithDuplicateEmail()
    {

        factory(User::class)->create([
            'email' => $email = $this->faker->email,
        ]);

        $user = factory(User::class)->raw([
            'email' => $email,
        ]);

        $response = $this->postJson("$this->baseurl/signup", $user);
        $response->assertStatus(422);

    }

    /**
     * Test case for email login.
     *
     * @return void
     */
    public function testLoginWithEmail()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt($password = $this->faker->password),
        ]);

        $data = [
            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->postJson("$this->baseurl/signin", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "token",
                "user",
            ],
            "status",
            "message",
        ]);
    }

    /**
     * Test case for invalid login credentials.
     *
     * @return void
     */
    public function testLoginWithInvalidCredentials()
    {
        $user = factory(User::class)->create([
            'password' => bcrypt($this->faker->password),
        ]);

        $data = [
            'email' => $user->email,
            'password' => $this->faker->password,
        ];

        $response = $this->postJson("$this->baseurl/signin", $data);
        $response->assertStatus(400);
        $response->assertJsonStructure([
            "data",
            "status",
            "message",
        ]);
    }

}
