<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\ContactList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ContactListController extends Controller
{

    public function contacts()
    {
        $contacts = ContactList::where('user_id',auth()->user()->id)->get();

        if ($contacts->isEmpty()) {
            $notify[] = 'No contact found';
            return response()->json([
                'remark'=>'no_contact',
                'status'=>'error',
                'message'=>['error'=>$notify],
            ]);
        }

        return response()->json([
            'remark'=>'contact',
            'status'=>'success',
            'data'=>$contacts,
        ]);
    }

    public function contactInsert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => [
                'required',
                'regex:/^\+?[\d\-\s]+$/',
                Rule::unique('contact_lists')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'=>'validation_error',
                'status'=>'error',
                'message'=>['error'=>$validator->errors()->all()],
            ]);
        }

        $contact = new ContactList();
        $contact->user_id = auth()->id();
        $contact->name = $request->name;
        $contact->mobile = $request->mobile;
        $contact->save();

        $notify[] = 'Contact saved successfully';
        return response()->json([
            'remark'=>'contact_added',
            'status'=>'success',
            'message'=>$notify,
        ]);
    }

    public function contactUpdate(Request $request, $id)
    {
        $contact = ContactList::where('user_id', auth()->id())->find($id);

        if (!$contact) {
            return response()->json([
                'remark' => 'contact_not_found',
                'status' => 'error',
                'message' => ['error' => 'Contact not found'],
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => [
                'required',
                'regex:/^\+?[\d\-\s]+$/',
                Rule::unique('contact_lists')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                })->ignore($id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $contact->name = $request->name;
        $contact->mobile = $request->mobile;
        $contact->save();

        return response()->json([
            'remark' => 'contact_updated',
            'status' => 'success',
            'message' => ['Contact updated successfully'],
        ]);
    }

    public function contactDelete($id)
    {

        $contact = ContactList::where('user_id', auth()->id())->find($id);

        if(!$contact) {
            return response()->json([
                'remark' => 'contact_not_found',
                'status' => 'error',
                'message' => ['error' => 'Contact not found'],
            ]);
        }
        $contact->delete();

        $notify[] = 'Contact deleted successfully';

        return response()->json([
            'remark' => 'contact_deleted',
            'status' => 'success',
            'message' => $notify,
        ]);
    }
}
