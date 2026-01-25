<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $notification = array(
            'message' => 'User Logout Successfully',
            'alert-type' => 'success'
        );

        return redirect('/login')->with($notification);
    }

    public function Profile(){
        $id = Auth::user()->id; //get authenticated use info
        $adminData = User::find($id);//find which user is logged in

        return view('admin.admin_profile_view', compact('adminData'));
    }

    public function EditProfile(){
        $id = Auth::user()->id; //get authenticated use info
        $editData = User::find($id);//find which user is logged in

        return view('admin.admin_profile_edit', compact('editData'));
    }

    // public function StoreProfile(Request $request){
    //     $id = Auth::user()->id;
    //     $data = User::find($id);
    //     $data->name = $request->name;
    //     $data->email = $request->email;
    //     $data->phone = $request->phone;
    //     $data->username = $request->username;

    //     if ($request->file('profile_image')) {
    //        $file = $request->file('profile_image');
    //        @unlink(public_path('upload/admin_images/'.$data->photo));
    //        $filename = date('YmdHi').$file->getClientOriginalName();
    //        $file->move(public_path('upload/admin_images'),$filename);
    //        $data['profile_image'] = $filename;
    //     }
    //     $data->save();

    //     $notification = array(
    //         'message' => 'Admin Profile Updated Successfully',
    //         'alert-type' => 'success'
    //     );

    //     return redirect()->route('admin.profile')->with($notification);

    // }// End Method

    public function StoreProfile(Request $request)
{
    // Retrieve the authenticated user
    /** @var \App\Models\User $user */
    $user = Auth::user();

    // Update user data from the request
    $user->name     = $request->name;
    $user->email    = $request->email;
    $user->phone    = $request->phone;
    $user->username = $request->username;

    // Check if a new profile image has been uploaded
    if ($request->hasFile('profile_image')) {
        // Validate file type and size
        $request->validate([
            'profile_image' => 'image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        $file = $request->file('profile_image');

        // Remove the existing profile image if it exists
        if ($user->profile_image) {
            $existingPath = 'admin_images/' . $user->profile_image;
            if (Storage::disk('public')->exists($existingPath)) {
                Storage::disk('public')->delete($existingPath);
            }
        }

        // Store with a safe generated filename
        $filename = uniqid('admin_') . '.' . $file->getClientOriginalExtension();
        $file->storeAs('admin_images', $filename, 'public');

        // Update the user's profile image field with the new filename
        $user->profile_image = $filename;
    }

    // Save the updated user data
    $user->save();

    // Prepare a notification message
    $notification = [
        'message'    => 'Admin Profile Updated Successfully',
        'alert-type' => 'success'
    ];

    // Redirect back to the profile page with the notification
    return redirect()->route('admin.profile')->with($notification);
}


    public function ChangePassword(){
        return view('admin.admin_change_password');
    }// End Method

    public function UpdatePassword(Request $request){

        $validateData = $request->validate([
            'oldpassword' => 'required',
            'newpassword' => 'required|string|min:10|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/',
            'confirm_password' => 'required|same:newpassword',
        ], [
            'newpassword.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'newpassword.min' => 'Password must be at least 10 characters.',
        ]);
        //Changeing the password in the DB
        $hashedPassword = Auth::user()->password;
        if (Hash::check($request->oldpassword,$hashedPassword )) {
            $users = User::find(Auth::id());
            $users->password = bcrypt($request->newpassword);
            $users->save();

            session()->flash('message','Password Updated Successfully');
            return redirect()->back();
        } else{
            session()->flash('message','Old password is not match');
            return redirect()->back();
        }

    }// End Method

    //////////////Admin User All Method ///////

    public function AllAdmin(){

    $alladminuser = User::latest()->get();
    return view('backend.admin.all_admin',compact('alladminuser'));

    }// End Method

    public function AddAdmin(){

        $roles = Role::all();
        return view('backend.admin.add_admin',compact('roles'));
    }// End Method

    // public function StoreAdmin(Request $request){
    //     $user = new User();
    //     $user->name = $request->name;
    //     $user->email = $request->email;
    //     $user->username = $request->username;
    //     $user->phone = $request->phone;
    //     $user->password = Hash::make($request->password);
    //     $user->save();
    //     if ($request->roles) {
    //         $user->assignRole($request->roles);
    //     }
    //     $notification = array(
    //         'message' => 'New Admin User Created Successfully',
    //         'alert-type' => 'success'
    //     );
    //     return redirect()->route('all.admin')->with($notification);
    // }// End Method

    public function StoreAdmin(Request $request)
{
    // Validate the request
    $validated = $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users,email',
        'username' => 'required|string|max:255|unique:users,username',
        'phone'    => 'required|string|max:13',
        'password' => 'required|string|min:10|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/',
        'roles'    => 'nullable',
    ], [
        'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        'password.min' => 'Password must be at least 10 characters.',
    ]);

    // Create the user
    $user = new User();
    $user->name     = $validated['name'];
    $user->email    = $validated['email'];
    $user->username = $validated['username'];
    $user->phone    = $validated['phone'];
    $user->password = Hash::make($validated['password']);
    $user->save();

    // Assign role if provided
    if (!empty($request->roles)) {
        $role = Role::find($request->roles);
        if ($role) {
            $user->assignRole($role->name);
        }
    }

    // Prepare and send the notification
    $notification = [
        'message'    => 'New Admin User Created Successfully',
        'alert-type' => 'success'
    ];

    return redirect()->route('all.admin')->with($notification);
}

public function EditAdmin($id){

    $roles = Role::all();
    $adminuser = User::findOrFail($id);
    return view('backend.admin.edit_admin',compact('roles','adminuser'));

}// End Method

public function UpdateAdmin(Request $request)
{
    // Validate the incoming data
    $validated = $request->validate([
        'id'    => 'required|exists:users,id',
        'name'  => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $request->id,
        'phone' => 'required|string|max:13',
        'roles' => 'nullable',  // Adjust this based on whether you expect a single value or an array.
    ]);

    // Retrieve the admin user by ID
    $user = User::findOrFail($validated['id']);

    // Update user details
    $user->name  = $validated['name'];
    $user->email = $validated['email'];
    $user->phone = $validated['phone'];
    $user->save();

    // Process roles if provided.
    // Expecting roles as an array; if not, adjust accordingly.
    $roleIds = $request->roles ?? [];
    if (!is_array($roleIds)) {
        $roleIds = explode(',', $roleIds);
    }

    if (!empty($roleIds)) {
        // Retrieve the role names based on the IDs
        $roles = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
        // Sync the roles with the user
        $user->syncRoles($roles);
    } else {
        // Optionally, remove all roles if none are provided:
        $user->syncRoles([]);
    }

    // Prepare a notification message
    $notification = [
        'message'    => 'Admin User Updated Successfully',
        'alert-type' => 'success'
    ];

    // Redirect with the notification
    return redirect()->route('all.admin')->with($notification);
}

public function DeleteAdmin($id){

    $user = User::findOrFail($id);

    // Prevent deleting yourself
    if ($user->id === Auth::id()) {
        $notification = array(
            'message' => 'You cannot delete your own account while logged in.',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
    }

    try {
        // When user is deleted, foreign key constraints will automatically:
        // - Set expenses.created_by to NULL (via onDelete('set null'))
        // - Set expenses.approved_by to NULL (via onDelete('set null'))
        // - Set sms_logs.user_id to NULL (via onDelete('set null'))
        // - Delete scheduled_sms records (via onDelete('cascade'))
        // - Keep audit_trails records (user_id is not a foreign key)

        $user->delete();

        $notification = array(
            'message' => 'Admin User Deleted Successfully',
            'alert-type' => 'success'
        );
    } catch (\Exception $e) {
        $notification = array(
            'message' => 'Error deleting admin user: ' . $e->getMessage(),
            'alert-type' => 'error'
        );
    }

    return redirect()->back()->with($notification);

}// End Method

    /**
     * Suspend a user (Superadmin only)
     */
    public function SuspendAdmin($id){
        $user = User::findOrFail($id);

        // Prevent suspending yourself
        if ($user->id === Auth::id()) {
            $notification = array(
                'message' => 'You cannot suspend your own account.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }

        $user->status = 'suspended';
        $user->save();

        $notification = array(
            'message' => 'Admin User Suspended Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }// End Method

    /**
     * Activate a suspended user (Superadmin only)
     */
    public function ActivateAdmin($id){
        $user = User::findOrFail($id);

        $user->status = 'active';
        $user->save();

        $notification = array(
            'message' => 'Admin User Activated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }// End Method

    /**
     * Show change password form for a specific user (Superadmin only)
     */
    public function ChangeAdminPassword($id){
        $adminuser = User::findOrFail($id);

        // Prevent changing your own password through this route
        if ($adminuser->id === Auth::id()) {
            $notification = array(
                'message' => 'Use the profile settings to change your own password.',
                'alert-type' => 'info'
            );
            return redirect()->back()->with($notification);
        }

        return view('backend.admin.change_admin_password', compact('adminuser'));
    }// End Method

    /**
     * Update user password (Superadmin only)
     */
    public function UpdateAdminPassword(Request $request){
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:10|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/',
        ], [
            'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'new_password.min' => 'Password must be at least 10 characters.',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Prevent changing your own password through this route
        if ($user->id === Auth::id()) {
            $notification = array(
                'message' => 'Use the profile settings to change your own password.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        $notification = array(
            'message' => 'Admin Password Changed Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.admin')->with($notification);
    }// End Method


    //////////////// Database Backup Method //////////////////

    public function DatabaseBackup(){
        return view('admin.db_backup')->with('files',File::allFiles(storage_path('/app/Raha')));
    }// End Method

    public function BackupNow(){
        Artisan::call('backup:run');
          $notification = array(
            'message' => 'Database Backup Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }// End Method

    public function DownloadDatabase($getFilename){
        // Sanitize filename - only allow alphanumeric, dots, hyphens, underscores
        $getFilename = basename($getFilename);
        if (!preg_match('/^[\w\-. ]+$/', $getFilename)) {
            abort(403, 'Invalid filename');
        }

        $path = storage_path('app/Raha/'.$getFilename);
        if (!file_exists($path)) {
            abort(404, 'File not found');
        }
        return response()->download($path);
    }// End Method

    public function DeleteDatabase($getFilename){
        // Sanitize filename - only allow alphanumeric, dots, hyphens, underscores
        $getFilename = basename($getFilename);
        if (!preg_match('/^[\w\-. ]+$/', $getFilename)) {
            abort(403, 'Invalid filename');
        }

        Storage::delete('Raha/'.$getFilename);
        $notification = array(
            'message' => 'Database Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }// End Method

}
