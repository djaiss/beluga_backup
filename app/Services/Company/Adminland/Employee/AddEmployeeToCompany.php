<?php

namespace App\Services\Company\Adminland\Employee;

use App\Models\User\User;
use Illuminate\Support\Str;
use App\Services\BaseService;
use App\Models\Company\Company;
use App\Models\Company\Employee;
use App\Services\Contact\Avatar\GenerateAvatar;
use App\Services\Company\Employee\LogEmployeeAction;
use App\Services\Company\Adminland\Company\LogAuditAction;

class AddEmployeeToCompany extends BaseService
{
    /**
     * Get the validation rules that apply to the service.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company_id' => 'required|integer|exists:companies,id',
            'author_id' => 'required|integer|exists:users,id',
            'email' => 'required|email|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'permission_level' => 'required|integer',
            'send_invitation' => 'required|boolean',
            'is_dummy' => 'nullable|boolean',
        ];
    }

    /**
     * Add someone to the company.
     *
     * @param array $data
     * @return Employee
     */
    public function execute(array $data): Employee
    {
        $this->validate($data);

        $author = $this->validatePermissions(
            $data['author_id'],
            $data['company_id'],
            config('homas.authorizations.hr')
        );

        $employee = $this->createEmployee($data, $author);

        (new LogAuditAction)->execute([
            'company_id' => $data['company_id'],
            'action' => 'employee_added_to_company',
            'objects' => json_encode([
                'author_id' => $author->id,
                'author_name' => $author->name,
                'employee_id' => $employee->id,
                'employee_email' => $data['email'],
                'employee_first_name' => $data['first_name'],
                'employee_last_name' => $data['last_name'],
            ]),
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ]);

        if ($data['send_invitation']) {
            (new InviteUser)->execute([
                'company_id' => $data['company_id'],
                'author_id' => $author->id,
                'employee_id' => $employee->id,
            ]);
        }

        return $employee;
    }

    /**
     * Create the employee.
     *
     * @param array $data
     * @param User $author
     * @return Employee
     */
    private function createEmployee(array $data, User $author): Employee
    {
        $uuid = Str::uuid()->toString();

        $avatar = (new GenerateAvatar)->execute([
            'uuid' => $uuid,
            'size' => 200,
        ]);

        $employee = Employee::create([
            'company_id' => $data['company_id'],
            'uuid' => $uuid,
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'avatar' => $avatar,
            'permission_level' => $data['permission_level'],
            'is_dummy' => $this->valueOrFalse($data, 'is_dummy'),
        ]);

        (new LogEmployeeAction)->execute([
            'company_id' => $data['company_id'],
            'employee_id' => $employee->id,
            'action' => 'employee_created',
            'objects' => json_encode([
                'author_id' => $author->id,
                'author_name' => $author->name,
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
            ]),
        ]);

        return $employee;
    }
}
