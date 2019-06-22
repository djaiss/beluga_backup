<?php

namespace Tests\Unit\Services\Company\Adminland\Employee;

use Tests\TestCase;
use App\Models\User\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\Company\Adminland\Employee\AddEmployeeToCompany;

class AddEmployeeToCompanyTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_adds_an_employee_to_a_company()
    {
        $adminEmployee = $this->createAdministrator();
        $user = factory(User::class)->create([]);

        $request = [
            'company_id' => $adminEmployee->company_id,
            'author_id' => $adminEmployee->user->id,
            'email' => 'dwight@dundermifflin.com',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'permission_level' => config('homas.authorizations.user'),
            'send_invitation' => false,
        ];

        $employee = (new AddEmployeeToCompany)->execute($request);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'user_id' => null,
            'company_id' => $adminEmployee->company_id,
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
        ]);

        $this->assertNotNull($employee->avatar);
    }

    /** @test */
    public function it_logs_an_action()
    {
        $adminEmployee = $this->createAdministrator();
        $user = factory(User::class)->create([]);

        $request = [
            'company_id' => $adminEmployee->company_id,
            'author_id' => $adminEmployee->user->id,
            'email' => 'dwight@dundermifflin.com',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'permission_level' => config('homas.authorizations.user'),
            'send_invitation' => false,
        ];

        $employee = (new AddEmployeeToCompany)->execute($request);

        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $employee->company_id,
            'action' => 'employee_added_to_company',
        ]);

        $this->assertDatabaseHas('employee_logs', [
            'company_id' => $employee->company_id,
            'action' => 'employee_created',
        ]);
    }

    /** @test */
    public function it_fails_if_wrong_parameters_are_given()
    {
        $adminEmployee = $this->createAdministrator();
        $user = factory(User::class)->create([]);

        $request = [
            'company_id' => $adminEmployee->company_id,
            'last_name' => 'Schrute',
            'permission_level' => config('homas.authorizations.user'),
        ];

        $this->expectException(ValidationException::class);
        (new AddEmployeeToCompany)->execute($request);
    }
}
