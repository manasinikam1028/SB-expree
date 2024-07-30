<!-- resources/views/admin/pincode/actions.blade.php -->
 @if ($data->status =="Delivered")
                      <a href="{{route('Booking.show',$data->id)}}" class="btn btn-sm"><i class="fas fa-copy"></i> View</a>
                    @else
                        <a href="{{route('Booking.show',$data->id)}}" class="btn btn-sm"><i class="fas fa-edit"></i> Edit</a>
                    @endif
                        <a href="{{route('Booking-invoice',$data->id)}}" target="_blank"  class="btn btn-sm"><i class="fas fa-print"></i> Print</a>
