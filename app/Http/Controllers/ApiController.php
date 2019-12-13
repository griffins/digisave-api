<?php

namespace App\Http\Controllers;

use App\Identity;
use App\Member;
use App\Organization;
use App\Service;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function enroll()
    {
        if (\request()->isJson()) {
            $payload = json_decode(\request()->getContent(), true);
        } else {
            $payload = \request()->all();
        }
        $this->protectHere($payload);
        //todo check uniqueness of service reference
        $this->validateWithDetails($payload,
            [
                'name' => 'required',
                'country_code' => 'required',
                'service.code' => 'required|exists:services,code',
                'service.reference' => 'required',
                'email' => 'required|email',
                'identity' => 'required',
                'phone_number' => 'required|phone:country_code'
            ]);

        $query = Identity::query()
            ->where('type', $payload['identity']['type'])
            ->where('number', $payload['identity']['number']);

        DB::beginTransaction();

        if ($query->exists()) {
            $member = $query->firstOrFail()->member;
            $member->fill(collect($payload)->only('name', 'email', 'phone_number')->toArray());
            $member->save();
        } else {
            $member = new Member(collect($payload)->only('name', 'email', 'phone_number', 'country_code')->toArray());
            $member->save();
            $identity = new Identity(collect($payload['identity'])->only('type', 'number')->toArray());
            $member->identities()->save($identity);
        }
        $service = Service::query()->where('code', $payload['service']['code'])->firstOrFail();
        if (!$member->services()
            ->where('service_id', $service->id)
            ->exists()) {
            $member->services()->attach($service, ['reference' => $payload['service']['reference']]);
        };

        DB::commit();
        return $member;
    }

    public function deposit()
    {
        if (\request()->isJson()) {
            $payload = json_decode(\request()->getContent(), true);
        } else {
            $payload = \request()->all();
        }
        $this->protectHere($payload);
        //todo check uniqueness of service reference
        $this->validateWithDetails($payload,
            [
                'narration' => 'required',
                'member.service.code' => 'required|exists:services,code',
                'member.service.reference' => 'required',
                'type' => 'required|in:deposit',
                'member.identity.number' => 'required|exists:identities,number',
                'member.identity.type' => 'required',
                'amount' => 'required|min:1'
            ]);

        DB::beginTransaction();
        $service = Service::query()->where('code', $payload['member']['service']['code'])->firstOrFail();
        $member = Identity::query()
            ->where('type', $payload['member']['identity']['type'])
            ->where('number', $payload['member']['identity']['number'])->firstOrFail()->member;

        $transaction = new Transaction(collect($payload)->only('narration', 'amount', 'type')->toArray());
        $transaction->service_id = $service->id;
        $transaction->service_reference = $payload['member']['service']['reference'];
        $transaction->member_id = $member->id;
        $transaction->reference = \Ramsey\Uuid\Uuid::uuid4()->getHex();
        $transaction->save();

        DB::commit();
        return $transaction;
    }

    private function protectHere(array $payload)
    {
        $code = coalesce(array_get($payload, 'member.service.code'), array_get($payload, 'service.code'));
        $service = Service::query()->where('code', $code)->first();
        if ($service) {
            $token = \request()->bearerToken();
            if ($service->organization->apps()->where('token', $token)->count() == 0) {
                abort(403, "Invalid service reference");
            }
        } else {
            abort(403, "Invalid service reference");
        }
    }
}
