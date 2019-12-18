<?php

namespace App\Http\Controllers;

use App\App;
use App\Organization;
use Illuminate\Http\Request;
use League\ISO3166\ISO3166;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:organizations',
            'type' => 'in:funds_manager,merchant'
        ],
            [
                'in' => 'The organization type is invalid, Should be either funds_manager or merchant',
                'unique' => 'The organization name has already been taken'
            ]
        );
        return Organization::query()->create(\request()->only('name', 'type'));
    }

    /**
     * Display the specified resource.
     *
     * @param Organization $organization
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function show(Organization $organization)
    {
        return Organization::with('services', 'users', 'apps')->find($organization->id);
    }

    /**
     * Display the specified resource.
     *
     * @param Organization $organization
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return user()->organizations()->with('services', 'apps', 'users')->get();
    }

    /**
     * Display the specified resource.
     *
     * @param Organization $organization
     * @return \Illuminate\Http\Response
     */
    public function token(Organization $organization)
    {
        if (\request()->isMethod('post')) {
            $organization->apps()->delete();
            $token = str_random(64);
            $organization->apps()->save(new  App(['token' => $token]));
        }
        return view('organization.token', compact('organization', 'token'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Organization $organization
     * @return Organization
     */
    public function update(Request $request, Organization $organization)
    {
        $organization->fill($request->only('name', 'type'));
        $organization->save();
        return $organization;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Organization::destroy($id);
    }
}
