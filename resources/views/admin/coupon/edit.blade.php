@extends('admin.layouts.app')

@section('content')

<!-- Content Header (Page header) -->
    <section class="content-header">					
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Category</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{route('coupons.index')}}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        @include('admin.message')
        <div class="container-fluid">
            <form action="post" name="discountUpdateForm" id="discountUpdateForm">
                <div class="card">
                    <div class="card-body">								
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code">Update Coupon Code</label>
                                    <input type="text" name="code" id="code" class="form-control" value="{{$discountCoupon->code}}">	
                                    <p></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" class="form-control" value="{{$discountCoupon->name}}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_uses">Max Uses</label>
                                    <input type="text" name="max_uses" id="max_uses" class="form-control" value="{{$discountCoupon->max_uses}}">	
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_uses_user">Max Uses User</label>
                                    <input type="text" name="max_uses_user" id="max_uses_user" class="form-control" value="{{$discountCoupon->max_uses_user}}">
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label for="type">Type</label>
                                    <select name="type" id="type" class="form-control">
                                        <option {{ ($discountCoupon->type == 'percent') ? 'selected' : '' }} value="percent">Parcent</option>
                                        <option {{ ($discountCoupon->type == 'fixed') ? 'selected' : '' }} value="fixed">Fixed</option>
                                    </select>
                                </div>
                            </div>	
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_amount">Discount Amount</label>
                                    <input type="number" name="discount_amount" id="discount_amount" class="form-control" value="{{$discountCoupon->discount_amount}}">	
                                    <p></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_amount">Minimum Amount</label>
                                    <input type="text" name="min_amount" id="min_amount" class="form-control" value="{{$discountCoupon->min_amount}}">	
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option {{ ($discountCoupon->status == 1) ? 'selected' : '' }} value="1">Yes</option>
                                    <option {{ ($discountCoupon->status == 0) ? 'selected' : '' }} value="0">No</option>
                                </select>
                                </div>
                            </div>	
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="starts_at">Starts At</label>
                                    <input autocomplete="off" type="text" name="starts_at" id="starts_at" class="form-control" value="{{$discountCoupon->starts_at}}">
                                    <p></p>	
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expires_at">Expires At</label>
                                    <input autocomplete="off" type="text" name="expires_at" id="expires_at" class="form-control" value="{{$discountCoupon->expires_at}}">
                                    <p></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" cols="50"class="form-control" rows="5">{{$discountCoupon->description}}</textarea>
                                </div>  
                            </div>						
                        </div>
                    </div>							
                </div>
                <div class="pb-5 pt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <!-- <a href="brands.html" class="btn btn-outline-dark ml-3">Cancel</a> -->
                </div>
            </form>
        </div>
        <!-- /.card -->
    </section>
    <!-- /.content -->

 @endsection

 @section('customjs')
<script>
    $(document).ready(function(){
        $('#starts_at').datetimepicker({
            // options here
            format:'Y-m-d H:i:s',
        });

        $('#expires_at').datetimepicker({
            // options here
            format:'Y-m-d H:i:s',
        });
    });
    
    $('#discountUpdateForm').submit(function(event){
    event.preventDefault();
    var element = $(this);

    $.ajax({
        url: '{{ route("coupons.update",$discountCoupon->id ) }}',
        type: 'put',
        data: element.serializeArray(),
        dataType: 'json',
        success: function(response){
            
            if (response['status'] == true){

                window.location.href="{{ route('coupons.index') }}";
                
                $("#code").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                $("#discount_amount").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                $("#expires_at").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
            } else {
                
                var errors = response['errors'];

                if(errors['code']){
                    $("#code").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['code']);
                } else{
                    $("#code").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                }
                if(errors['discount_amount']){
                    $("#discount_amount").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['discount_amount']);
                } else{
                    $("#discount_amount").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                }
                
                if(errors['expires_at']){
                    $("#expires_at").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['expires_at']);
                } else{
                    $("#expires_at").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                }
                

            }
        },
        error: function(jqXHR, exception){
            console.log("Something went wrong");
        }
    });
});

</script>

@endsection
