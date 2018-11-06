<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserAddress;
use Illuminate\Validation\Rule;
use Validator;

class UserAddressesController extends Controller
{
    public function index(Request $request)
    {
        return view('user_addresses.index', [
            'addresses' => $request->user()->addresses,
        ]);
    }

     public function create()
    {
        return view('user_addresses.create_and_edit', ['address' => new UserAddress()]);
    }
    public function store(Request $data)
    {
    	$data->flash();
        $request=$data->all();
            $v=Validator::make($request,[
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'zip' 			=> 'required',
            ]);
         if ($v->fails()) 
         {
            return redirect()->route('user_addresses.create')
                        ->withErrors($v)
                        ->withInput();
   		 }
   		 else
   		 {
   		 	$data->user()->addresses()->create($data->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        	]));

        return redirect()->route('user_addresses.index');
   		 }
	}
	public function update(UserAddress $user_address, Request $data)
    {	
    	if($user_address->user()->where('id',$data->user()->id)->get())
 		{
    	$data->flash();
        $request=$data->all();
            $v=Validator::make($request,[
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            ]);
         if ($v->fails()) 
         {
            return redirect()->route('user_addresses.create')
                        ->withErrors($v)
                        ->withInput();
   		 }
   		 else
   		 {
   		 	$user_address->update($data->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        	]));

        return redirect()->route('user_addresses.index');
   		 }
   		}
   		else{
   			return redirect()->route('login');
   		}
	}

	public function edit(UserAddress $user_address)
    {
        return view('user_addresses.create_and_edit', ['address' => $user_address]);
    }
    public function destroy(UserAddress $user_address,Request $data)
    {
    	if($user_address->user()->where('id',$data->user()->id)->get())
    	{
    		$user_address->delete();

        	return redirect()->route('user_addresses.index');
    	}
    }

}
