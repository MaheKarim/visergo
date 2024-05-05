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
        $contacts = ContactList::where('user_id',auth()->id())->get();

        if ($contacts->isEmpty()) {
            return formatResponse('no_contact', 'error', 'No contact found', []);
        }

        return formatResponse('contact_list', 'success', 'Contact list', $contacts);
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
            return formatResponse('validation_error', 'error', $validator->errors()->all(), []);
        }

        $contact = new ContactList();
        $contact->user_id = auth()->id();
        $contact->name = $request->name;
        $contact->mobile = $request->mobile;
        $contact->save();

        return formatResponse('contact_added', 'success', 'Contact saved successfully', $contact);
    }

    public function contactUpdate(Request $request, $id)
    {
        $contact = ContactList::where('user_id', auth()->id())->find($id);

        if (!$contact) {
            return formatResponse('contact_not_found', 'error', 'Contact not found', []);
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
            return formatResponse('validation_error', 'error', $validator->errors()->all(), []);
        }

        $contact->name = $request->name;
        $contact->mobile = $request->mobile;
        $contact->save();

        return formatResponse('contact_updated', 'success', 'Contact updated successfully', $contact);
    }

    public function contactDelete($id)
    {

        $contact = ContactList::where('user_id', auth()->id())->find($id);

        if(!$contact) {
            return formatResponse('contact_not_found', 'error', 'Contact not found', []);
        }
        $contact->delete();

        return formatResponse('contact_deleted', 'success', 'Contact deleted successfully', []);
    }
}
