<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Notifications\SendEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function addView()
    {
        if (Auth::id()) {
            if (Auth::user()->usertype==1) {
                return view('admin.add_doctor');
            }

            return redirect()->back();
        }

        return redirect('login');
    }

    public function upload(Request $request)
    {
        $doctor = new Doctor();
        $image = $request->file;
        $imageName = time() . '.' . $image->getClientoriginalExtension();
        $request->file->move('doctorimage', $imageName);

        $doctor->image = $imageName;
        $doctor->name = $request->name;
        $doctor->phone = $request->number;
        $doctor->room = $request->room;
        $doctor->speciality = $request->speciality;

        $doctor->save();

        return redirect()->back()->with('message', 'Doctor Added Successfully');
    }

    public function showAppointment()
    {
        if (Auth::id()) {
            if (Auth::user()->usertype === '1') {
                $data = Appointment::all();

                return view('admin.showappointment', compact('data'));
            }

            return redirect()->back();
        }

        return redirect('login');
    }

    public function approved($id)
    {
        $data = Appointment::find($id);
        $data->status = 'approved';
        $data->save();

        return redirect()->back();
    }

    public function canceled($id)
    {
        $data = Appointment::find($id);
        $data->status = 'canceled';
        $data->save();

        return redirect()->back();
    }

    public function showdoctor()
    {
        $data = Doctor::all();

        return view('admin.showdoctor', compact('data'));
    }

    public function deleteDoctor($id)
    {
        $data = Doctor::find($id);
        $data->delete();

        return redirect()->back();
    }

    public function updateDoctor($id)
    {
        $data = doctor::find($id);

        return view('admin.update_doctor', compact('data'));
    }

    public function editDoctor(Request $request, $id)
    {
        $doctor = Doctor::find($id);
        $doctor->name = $request->name;
        $doctor->phone = $request->phone;
        $doctor->speciality = $request->speciality;
        $doctor->room = $request->room;

        $image = $request->file;

        if ($image) {
            $imagename = time() . '.' . $image->getClientOriginalExtension();
            $request->file->move('doctorimage', $imagename);
            $doctor->image = $imagename;
        }

        $doctor->save();

        return redirect()->back()->with('message','Doctor Details Updated Successfully');
    }

    public function emailView($id)
    {
        $data = Appointment::find($id);

        return view('admin.email_view', compact('data'));
    }

    public function sendEmail(Request $request, $id)
    {
        $data = Appointment::find($id);
        $details = [
            'greeting' => $request->greeting,
            'body' => $request->body,
            'actiontext' => $request->actiontext,
            'actionurl' => $request->greeting,
            'endpart' => $request->enpart
        ];

        Notification::send($data, new SendEmailNotification($details));

        return redirect()->back()->with('message', 'Email send is successful');
    }
}
