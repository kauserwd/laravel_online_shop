@extends('admin.layouts.app')

@section('content')

				<!-- Content Header (Page header) -->
<!-- Content Header (Page header) -->
<section class="content-header">					
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit User</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{route('users.index')}}" class="btn btn-primary">Back</a>
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
        <form action="" name="updateUserForm" id="updateUserForm">
            <div class="card">
                <div class="card-body">								
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ $user->name}}">	
                                <p></p>
                            </div>  
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email">Email</label>
                                <input type="text" name="email" id="email" class="form-control" value="{{ $user->email}}">
                                <p></p>	
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password">Password</label>
                                <input type="number" name="password" id="password" class="form-control" placeholder="Password">
                                <span>To change password you have to enter a value, otherwise leave blank. </span>
                                <p></p>	
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="{{ $user->phone}}">
                                <p></p>	
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option {{ ($user->status == 1) ? 'selected' : '' }} value="1">Active</option>
                                <option {{ ($user->status == 0) ? 'selected' : '' }} value="0">Block</option>
                            </select>
                            </div>
                        </div>									
                    </div>
                </div>							
            </div>
            <div class="pb-5 pt-3">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->

 @endsection

 @section('customjs')
<script>
    
    $('#updateUserForm').submit(function(event){
    event.preventDefault();
    var element = $(this);

    $.ajax({
        url: '{{ route("users.update", $user->id) }}',
        type: 'put',
        data: element.serializeArray(),
        dataType: 'json',
        success: function(response){
            
            if (response['status'] == true){

                
                $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                $("#email").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                $("#phone").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                $("#password").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                window.location.href="{{ route('users.index') }}";
            } else {
                
                    var errors = response['errors'];

                    if(errors['name']){
                        $("#name").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['name']);
                    } else{
                        $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }

                    if(errors['email']){
                        $("#email").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['email']);
                    } else{
                        $("#email").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
                    if(errors['phone']){
                        $("#phone").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['phone']);
                    } else{
                        $("#phone").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
                    if(errors['password']){
                        $("#password").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['password']);
                    } else{
                        $("#password").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
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
