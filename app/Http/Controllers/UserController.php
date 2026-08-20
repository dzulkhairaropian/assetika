<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\Position;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'department', 'company', 'position', 'location']);

        if ($request->filled('search')) {
            $search = $request->search;
            $like = '%' . $search . '%';
            $query->where(function($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('phone', 'like', $like)
                  ->orWhereHas('department', fn($d) => $d->where('name', 'like', $like))
                  ->orWhereHas('company', fn($c) => $c->where('name', 'like', $like))
                  ->orWhereHas('position', fn($p) => $p->where('name', 'like', $like))
                  ->orWhereHas('location', fn($l) => $l->where('name', 'like', $like))
                  ->orWhereHas('roles', fn($r) => $r->where('name', 'like', $like));
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('is_active') && $request->is_active !== null && $request->is_active !== '') {
            $query->where('is_active', (bool)$request->is_active);
        }

        $users = $query->latest()->paginate(50)->withQueryString();
        
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
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        $roles = collect($request->roles)->map(fn($val) => is_numeric($val) ? (int)$val : $val)->toArray();
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
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : false,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $roles = collect($request->roles)->map(fn($val) => is_numeric($val) ? (int)$val : $val)->toArray();
        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
