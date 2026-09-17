<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100, 250, 500]) ? (int) $request->input('per_page') : 50;
        $users = User::with(['roles', 'department', 'company', 'position', 'location'])
            ->filter($request->all())
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $roles = Role::all();
        $departments = Department::all();

        return view('pages.users.index', compact('users', 'roles', 'departments'));
    }

    public function show(User $user)
    {
        return redirect()->route('users.edit', $user);
    }

    public function create()
    {
        $roles = Role::all();
        $companies = Company::all();
        $departments = Department::all();
        $locations = Location::all();
        $positions = Position::all();

        return view('pages.users.create', compact('roles', 'companies', 'departments', 'locations', 'positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'roles' => 'required|array',
            'company_id' => 'nullable|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'location_id' => 'nullable|exists:locations,id',
            'position_id' => 'nullable|exists:positions,id',
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'company_id' => $request->company_id,
            'department_id' => $request->department_id,
            'location_id' => $request->location_id,
            'position_id' => $request->position_id,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        $roles = collect($request->roles)->map(fn ($val) => is_numeric($val) ? (int) $val : $val)->toArray();
        $user->assignRole($roles);

        if ($request->has('create_another')) {
            return redirect()->route('users.create')->with('success', 'User created successfully.');
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        $companies = Company::all();
        $departments = Department::all();
        $locations = Location::all();
        $positions = Position::all();

        return view('pages.users.edit', compact('user', 'roles', 'userRoles', 'companies', 'departments', 'locations', 'positions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'roles' => 'required|array',
            'company_id' => 'nullable|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'location_id' => 'nullable|exists:locations,id',
            'position_id' => 'nullable|exists:positions,id',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_id' => $request->company_id,
            'department_id' => $request->department_id,
            'location_id' => $request->location_id,
            'position_id' => $request->position_id,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : false,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $roles = collect($request->roles)->map(fn ($val) => is_numeric($val) ? (int) $val : $val)->toArray();
        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $ids = array_diff($request->ids, [auth()->id()]);

        if (! empty($ids)) {
            User::whereIn('id', $ids)->delete();
        }

        return redirect()->route('users.index')->with('success', 'Selected users deleted successfully.');
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'is_active' => 'required|boolean',
        ]);

        $status = (bool) $request->is_active;

        User::whereIn('id', $request->ids)->update(['is_active' => $status]);

        $statusText = $status ? 'activated' : 'deactivated';

        return redirect()->route('users.index')->with('success', "Selected users {$statusText} successfully.");
    }
}
