@extends('admin.layouts.app')

@section('content')

				<!-- Content Header (Page header) -->
<!-- Content Header (Page header) -->
<section class="content-header">					
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Page</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{route('pages.index')}}" class="btn btn-primary">Back</a>
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
        <form action="" name="pageUpdateForm" id="pageUpdateForm">
            <div class="card">
                <div class="card-body">								
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Page Name</label>
                                <input type="text" name="name" id="name" class="form-control" Value="{{ $page->name }}">	
                                <p></p>
                            </div>  
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="slug">Page Slug</label>
                                <input readonly type="text" name="slug" id="slug" class="form-control" Value="{{ $page->slug }}">
                                <p></p>	
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="content">Content</label>
                                <textarea name="content" id="content" cols="30" rows="10" class="summernote" placeholder="Content">{{ $page->content }}</textarea>
                            </div>
                        </div>									
                    </div>
                </div>							
            </div>
            <div class="pb-5 pt-3">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('pages.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
            </div>
        </form>
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->

 @endsection

 @section('customjs')
<script>
    
    $('#pageUpdateForm').submit(function(event){
    event.preventDefault();
    var element = $(this);

    $.ajax({
        url: '{{ route("pages.update",$page->id) }}',
        type: 'put',
        data: element.serializeArray(),
        dataType: 'json',
        success: function(response){
            
            if (response['status'] == true){
                
                $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                $("#slug").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                window.location.href="{{ route('pages.index') }}";
            } else {
                
                    var errors = response['errors'];

                    if(errors['name']){
                        $("#name").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['name']);
                    } else{
                        $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }

                    if(errors['slug']){
                        $("#slug").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['slug']);
                    } else{
                        $("#slug").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
                    }
            }
        },
        error: function(jqXHR, exception){
            console.log("Something went wrong");
        }
    });
});

$('#name').change(function(){
    element = $(this);
    $.ajax({
            url: '{{ route("getSlug") }}',
            type: 'get',
            data: {title: element.val()},
            dataType: 'json',
            success: function(response){
                    if(response['status']==true){
                    $("#slug").val(response['slug']); 
                    }
                }
            });
    });

</script>

@endsection
