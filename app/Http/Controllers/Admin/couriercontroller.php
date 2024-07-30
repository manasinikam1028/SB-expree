<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\booking;
use App\Models\bookinglog;
use Auth;
use Session;
use DB;
use DataTables;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PincodeImport;
use App\Http\Controllers\admin\DateTime;
class couriercontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
 
    public function index()
        {
        $datas = booking::select(['id', 'booking_date', 'created_at', 'forwordingno', 'cust_name', 'pickuplocation', 'deliverylocation', 'status',]);

        return Datatables::of($datas)
            ->addColumn('action', function ($data) {
                return view('admin.booking.actions', compact('data'))->render();
            })
            ->make(true);
    }
    
// public function index()
// {
//  $datas = booking::select(['id', 'booking_date', 'created_at', 'forwordingno', 'cust_name', 'pickuplocation', 'deliverylocation', 'status',]);

//     return Datatables::of($datas)
//         ->addColumn('booking_date', function ($data) {
//             // Format the booking_date column to include both date and time
//             return $data->booking_date->format('Y-m-d H:i:s'); // Adjust the format as needed
//         })
//         ->addColumn('action', function ($data) {
//             return view('admin.booking.actions', compact('data'))->render();
//         })
//         ->make(true);
// }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      
        $datas = booking::where('forwordingno',$request->forwordingno)->exists();
        if($datas == "True"){
            session()->flash('alert-warning', 'forwording no Already Exist');
            return redirect('/Admin/booking');
            }
        $newscanpoint = new booking;
        $newscanpoint->cust_name = $request->cust_name;
        $newscanpoint->forwordingno = $request->forwordingno;
        $newscanpoint->refrenceno = $request->refrenceno;
        $newscanpoint->pickuplocation = $request->pickuplocation;
        $newscanpoint->deliverylocation = $request->deliverylocation;
        $newscanpoint->product_type = $request->product_type;
        $newscanpoint->content = $request->content;
        $newscanpoint->weight = $request->weight;
        $newscanpoint->vol_weight = $request->vol_weight;
        $newscanpoint->charg_weight = $request->charg_weight;
        $newscanpoint->client_name = $request->client_name;
        $newscanpoint->pickupaddress = $request->pickupaddress;
        $newscanpoint->pickup_name = $request->pickup_name;
        $newscanpoint->pickupcity = $request->pickupcity;
        $newscanpoint->pickup_pincode = $request->pickup_pincode;
        $newscanpoint->sendercontactno = $request->sendercontactno;
        $newscanpoint->con_client_name = $request->con_client_name;
        $newscanpoint->receiveraddress = $request->receiveraddress;
        $newscanpoint->receiverstate = $request->reciverstate;
        $newscanpoint->receivercity = $request->receivercity;
        $newscanpoint->receiver_pincode = $request->receiver_pincode;
        $newscanpoint->receivercontactno = $request->receivercontactno;
        $newscanpoint->status = 'Shipped';
        $newscanpoint->booking_date = $request->booking_date;
        $newscanpoint->save();
      
        $newentry = new bookinglog;
        $newentry->bookingno = $newscanpoint->id;
        $newentry->currentstatus = $request->pickuplocation;
        $newentry->status = 'Shipped';
        $newentry->remark = 'Shipped';
        $newentry->createdbyy = Auth::id();
        $newentry->save();

        session()->flash('alert-success', 'Booking Created Successfully');
        return redirect('/Admin/booking');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = booking::where('id',$id)->first();
        if(!$data){
            session()->flash('alert-warning', 'Wrong forwording Number');
            return redirect('/Admin/booking');
            }
        $datalogs = bookinglog::where('bookingno', $data->id)->get();
        if($data->status =="Delivered"){
            return view('admin.booking.show',compact('data','datalogs'));
        }
        return view('admin.booking.edit',compact('data','datalogs'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showdetails($id)
    {
      $datas= booking::find($id);  
          return view('admin.booking.show',compact('data','datas'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
        'currentstatus' => 'required',
        'status' => 'required',
        ]);
        
        $data = booking::where('id',$id)->exists();
     
        if($data ==false){
            session()->flash('alert-warning', 'Booking Not Found');
            return back();
            }
        booking::where('id',$id)->update(['status' => $request->status]);
        $newentry = new bookinglog;
        $newentry->bookingno = $id;
        $newentry->currentstatus = $request->currentstatus;
        $newentry->createdbyy = Auth::id();
        $newentry->status = $request->status;
        $newentry->remark = $request->remark;
        $newentry->deliverydate = $request->deliverydate;
        $newentry->save();
        session()->flash('alert-success', 'Booking Updated Successfully');
        return redirect('/Admin/booking');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function updateall($id)
    // {
        
    //      $request->validate([
    //     'cust_name' => 'required',
    //     'forwordingno' => 'required',
    //     ]);
        
    // }
    
    public function updateall(Request $request, $id)
    { 
        // dd('new');
    $booking = booking::findOrFail($id);
 
    $existingBooking = booking::where('forwordingno', $request->forwordingno)->where('id', '!=', $id)->exists();
    if ($existingBooking) {
        session()->flash('alert-warning', 'Forwarding number already exists');
        return redirect()->back();
    }  
    $booking->update([
        'cust_name' => $request->cust_name,
        'forwordingno' => $request->forwordingno,
        'refrenceno' => $request->refrenceno,
        'pickuplocation' => $request->pickuplocation,
        'deliverylocation' => $request->deliverylocation,
        'product_type' => $request->product_type,
         'content' => $request->content,
        'weight' => $request->weight,
        'vol_weight' => $request->vol_weight,
        'charg_weight' => $request->charg_weight,
        'client_name' => $request->client_name,
        'pickupaddress' => $request->pickupaddress,
        'pickup_name' => $request->pickup_name,
        'pickupcity' => $request->pickupcity,
        'pickup_pincode' => $request->pickup_pincode,
        'sendercontactno' => $request->sendercontactno,
        'con_client_name' => $request->con_client_name,
        'receiveraddress' => $request->receiveraddress,
        'receiverstate' => $request->receiverstate,
        'receivercity' => $request->receivercity,
        'receiver_pincode' => $request->receiver_pincode,
        'receivercontactno' => $request->receivercontactno,
        'booking_date' => $request->booking_date,
    ]);

    session()->flash('alert-success', 'Booking updated successfully');
    return redirect('/Admin/booking');
}
    

    
    public function invoice($id)
    {
 
            $datas= booking::find($id); 
            return view('admin/booking/invoice', compact('datas'));  
    }
    
    
    
    
//     public function BulkPincode(Request $request)
// {
   
//     $this->validateExcelUpload($request);
     
//     $filePath = $this->handleFileUpload($request);
    
//     if (!$filePath) {
//         return redirect()->back()->with('alert-error', 'Corrupt file or data missing');
//     }

//     $spreadsheet = IOFactory::load($filePath);
   
//     $sheet = $spreadsheet->getActiveSheet();
//     $sheetData = $this->readExcelSheet($sheet, 'A2:M');
    
//     $insertData = [];
//     foreach ($sheetData as $row) {
//         $existingRecord = DB::table('Pincode')->where('pincode', $row[0])->first();

//         if (!$existingRecord) {
//             $insertData[] = [
//                 'pincode' => $row[0],
//                 'area' => $row[1],
//                 'district' => $row[2],
//                 'state' => $row[3],
//                 'air-servie' => $row[4],
//                 'edlkmair' => $row[5],
//                 'embargo' => $row[6],
//                 'tat-air' => $row[7],
//                 'surface-service' => $row[8],
//                 'edlkmsurface' => $row[9],
//                 'tat-surface' => $row[10],
//                 'dp-service' => $row[11],
//                 'tatdp' => $row[12],
//             ];
//         }
//     }
//     if (!empty($insertData)) {
//     DB::table('Pincode')->insert($insertData);
//     }

//     return redirect('/Admin/Pincode')->with('alert-success', 'Pincode data added successfully');
// }

public function BulkPincode(Request $request)
{
    $this->validateExcelUpload($request);

    $filePath = $this->handleFileUpload($request);

    if (!$filePath) {
        return redirect()->back()->with('alert-error', 'Corrupt file or data missing');
    }
    Excel::import(new PincodeImport, $filePath);
    
    // $array = Excel::toArray([], $filePath);

    // $this->processArray($array);
    // Excel::filter('chunk')->import(new PincodeImport, $filePath, null, true, null, 'Xlsx', function ($results) {
    //     $this->processChunk($results);
    // });
    
    return redirect('/Admin/Pincode')->with('alert-success', 'Pincode data added successfully');
}

private function processChunk($results)
{
    $insertData = [];

    foreach ($results->toArray() as $row) {
        $existingRecord = DB::table('Pincode')->where('pincode', $row[0])->first();

        if (!$existingRecord) {
            $insertData[] = [
                'pincode' => $row[0],
                'area' => $row[1],
                'district' => $row[2],
                'state' => $row[3],
                'air-servie' => $row[4],
                'edlkmair' => $row[5],
                'embargo' => $row[6],
                'tat-air' => $row[7],
                'surface-service' => $row[8],
                'edlkmsurface' => $row[9],
                'tat-surface' => $row[10],
                'dp-service' => $row[11],
                'tatdp' => $row[12],
            ];
        }
    }

    if (!empty($insertData)) {
        DB::table('Pincode')->insert($insertData);
    }
}

// public function BulkBooking(Request $request)
// {
//     $this->validateExcelUpload($request);
//     $filePath = $this->handleFileUpload($request);

//     if (!$filePath) {
//         return redirect()->back()->with('alert-error', 'Corrupt file or data missing');
//     }
    
//     $spreadsheet = IOFactory::load($filePath);
//     $sheet = $spreadsheet->getActiveSheet();
//     $sheetData = $this->readExcelSheet($sheet, 'A3:Z');
//     // dd($sheetData);
    
//     $filteredData = [];
// foreach ($sheetData as $rowData) {
//     $isEmptyRow = true;
//     foreach ($rowData as $cellData) {
//         if (!empty($cellData)) {
//             $isEmptyRow = false;
//             break;
//         }
//     }
//     if (!$isEmptyRow) {
//         $filteredData[] = $rowData;
//     }
// }

// // dd($filteredData);

//     DB::beginTransaction();

//     try {
//         foreach ($filteredData as $row) {
//             if (in_array(null, $row, true)) {
//                 DB::rollBack();
//                 return redirect()->back()->with('alert-error', 'Data Missing');
//             }

//             $existingRecord = Booking::where('forwordingno', $row[2])->first();
       
//             if (!$existingRecord) {
//                 $newscanpoint = new booking;
//                 $newscanpoint->cust_name = $row[3];
//                 $newscanpoint->modeoftrans = $row[1];
//                 $newscanpoint->forwordingno = $row[2];
//                 $newscanpoint->pickuplocation = $row[4];
//                 $newscanpoint->deliverylocation = $row[5];
//                 $newscanpoint->product_type = $row[6];
//                 $newscanpoint->weight = $row[8];
//                 $newscanpoint->vol_weight = $row[9];
//                 $newscanpoint->charg_weight = $row[10];
//                 $newscanpoint->client_name = $row[11];
//                 $newscanpoint->pickupaddress = $row[12];
//                 // $newscanpoint->pickup_state = $row[];
//                 // $newscanpoint->pickupcity = $row[0];
//                 $newscanpoint->pickup_pincode = $row[13];
//                 $newscanpoint->sendercontactno = $row[14];
//                 $newscanpoint->con_client_name = $row[15];
//                 $newscanpoint->receiveraddress = $row[16];
//                 // $newscanpoint->receiverstate = $row[];
//                 // $newscanpoint->receivercity = $row[0];
//                 $newscanpoint->receiver_pincode = $row[17];
//                 $newscanpoint->receivercontactno = $row[18];
//                 $newscanpoint->refrenceno = $row[23];
//                 $newscanpoint->content = $row[24];
//                 $newscanpoint->status = 'Shipped';
//                 // $newscanpoint->booking_date = date("Y-m-d H:i:s", ($row[22] - 25569) * 86400);
//                 $dateTimestamp = ($row[22] - 25569) * 86400;
//                 // $timeSeconds = $row[23] * 86400;
//                 $timeSeconds = ($row[23] - floor($row[23])) * 86400;
//                 $dateTimeTimestamp = $dateTimestamp + $timeSeconds;
//                 $newscanpoint->booking_date = date("Y-m-d H:i:s", $dateTimeTimestamp);
                
//                 dd($newscanpoint);
//                 // $newscanpoint->save();
              
//                 $newentry = new bookinglog;
//                 $newentry->bookingno = $newscanpoint->id;
//                 $newentry->currentstatus = $row[4];
//                 $newentry->status = 'Shipped';
//                 $newentry->remark = 'Shipped';
//                 $newentry->deliverydate = date("Y-m-d H:i:s", (($row[22] - 25569) * 86400));
               
                
//                 $newentry->createdbyy = Auth::id();
//                 // $newentry->save();
//             }
//         }

//         DB::commit();
//         return redirect('/Admin/booking')->with('alert-success', 'Booking Created Successfully');
//     } catch (\Exception $e) {
//         DB::rollBack();
//         return redirect()->back()->with('alert-error', 'An error occurred while processing the data.');
//     }
// }

public function BulkBooking(Request $request)
{ 
    $this->validateExcelUpload($request);
     
    $filePath = $this->handleFileUpload($request);
 
    if (!$filePath) {
        return redirect()->back()->with('alert-error', 'Corrupt file or data missing');
    }
     
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
     
    $sheetData = $this->readExcelSheet($sheet, 'A3:Y');
     
    $filteredData = [];
    foreach ($sheetData as $rowData) {
        $isEmptyRow = true;
        foreach ($rowData as $cellData) {
            if (!empty($cellData)) {
                $isEmptyRow = false;
                break;
            }
        }
        if (!$isEmptyRow) {
            $filteredData[] = $rowData;
        }
    }
    // dd($filteredData);
    
    DB::beginTransaction();

    try { 
        foreach ($filteredData as $row) {
             
            if (in_array(null, $row, true)) {
                DB::rollBack();
                return redirect()->back()->with('alert-error', 'Data Missing');
            } 
            $existingRecord = Booking::where('forwordingno', $row[2])->first();
       
            if (!$existingRecord) {
                
                $newscanpoint = new Booking;
                $newscanpoint->cust_name = $row[3];
                $newscanpoint->modeoftrans = $row[1];
                $newscanpoint->forwordingno = $row[2];
                $newscanpoint->pickuplocation = $row[4];
                $newscanpoint->deliverylocation = $row[5];
                $newscanpoint->product_type = $row[6];
                $newscanpoint->weight = $row[8];
                $newscanpoint->vol_weight = $row[9];
                $newscanpoint->charg_weight = $row[10];
                $newscanpoint->client_name = $row[11];
                $newscanpoint->pickupaddress = $row[12];
                $newscanpoint->pickup_pincode = $row[13];
                $newscanpoint->sendercontactno = $row[14];
                $newscanpoint->con_client_name = $row[15];
                $newscanpoint->receiveraddress = $row[16];
                $newscanpoint->receiver_pincode = $row[17];
                $newscanpoint->receivercontactno = $row[18];
                $newscanpoint->refrenceno = $row[23];
                $newscanpoint->content = $row[24];
                $newscanpoint->status = 'Shipped';
                 
                // Include both date and time
                $bookingDateTime = Carbon::createFromFormat('d/m/Y H:i:s', $row[22])->format('Y-m-d H:i:s');
                $newscanpoint->booking_date = $bookingDateTime;
 
                $newscanpoint->save();
               
                $newentry = new BookingLog;
                $newentry->bookingno = $newscanpoint->id;
                $newentry->currentstatus = $row[4];
                $newentry->status = 'Shipped';
                $newentry->remark = 'Shipped';
                $newentry->deliverydate = $bookingDateTime;
                $newentry->createdbyy = Auth::id();
                $newentry->save();
            }
        }
 
        DB::commit();
         
        return redirect('/Admin/booking')->with('alert-success', 'Booking Created Successfully');
    } catch (\Exception $e) { 
        DB::rollBack();
        return redirect()->back()->with('alert-error', 'An error occurred while processing the data.');
    }
}


public function Bulkupdate(Request $request)
{
    $this->validateExcelUpload($request);

    $filePath = $this->handleFileUpload($request);

    if (!$filePath) {
        return redirect()->back()->with('alert-error', 'Corrupt file or data missing');
    }

    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $sheetData = $this->readExcelSheet($sheet, 'A2:E');

    DB::beginTransaction();

    try {
        foreach ($sheetData as $row) {
            if (in_array(null, $row, true)) {
                DB::rollBack();
                return redirect()->back()->with('alert-error', 'Data Missing');
            }

            $existingRecord = Booking::where('forwordingno', $row[0])
            ->where('status', '!=', 'Delivered')
            ->first();

            if ($existingRecord) {
                Booking::where('forwordingno', $row[0])->update(['status' => $row[3]]);

                $newentry = new bookinglog;
                $newentry->bookingno = $existingRecord->id;
                $newentry->currentstatus = $row[1];
                $newentry->createdbyy = Auth::id();
                $newentry->status = $row[3];
                $newentry->remark = $row[2];
                $newentry->deliverydate = date("Y-m-d", (($row[4] - 25569) * 86400));
                $newentry->save();
            }
        }

        DB::commit();
        return redirect('/Admin/booking')->with('alert-success', 'Booking Updated Successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('alert-error', 'An error occurred while processing the data.');
    }
}
public function bulkreport(Request $request)
    {

    if ($request->forwordingno != null) {

        $forwordingno = preg_replace('/\s+/', '', $request->forwordingno);
        $split = explode(",", $forwordingno);
        $datas = booking::whereIn('forwordingno', $split)->get();
    } else {
        $this->validateExcelUpload($request);
        $filePath = $this->handleFileUpload($request);
        if (!$filePath) {
            return redirect()->back()->with('alert-error', 'Corrupt file or data missing');
        }
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        $endRow = $sheet->getHighestRow();
        $range = 'A' . '2:' . 'A' . $endRow;
        $sheetData = $sheet->rangeToArray($range, null, true, false);
        // dd($sheetData);
        $datas = booking::whereIn('forwordingno', $sheetData)->get();
    
        
    }

    return view('admin/booking/Bulkinvoice', compact('datas'));
    }

private function validateExcelUpload($request)
{
    $request->validate([
        'excel_file' => 'required|mimes:xls,xlsx',
    ]);
}

private function handleFileUpload($request)
{
    $file = $request->file('excel_file');
    $folder = public_path('storage/excel/' . date('Y') . '/' . date('m'));
    
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
    
    $fileName = time() . '_' . $file->getClientOriginalName();
    $file->move($folder, $fileName);
    $filePath = $folder . '/' . $fileName;

    return is_readable($filePath) ? $filePath : null;
}

private function readExcelSheet($sheet, $range, $headerRow = 1)
{
    $startRow = $headerRow + 1;
    $endRow = $sheet->getHighestRow();
    $range = $range . $endRow;
    
    return $sheet->rangeToArray($range, null, true, false);
}
}
