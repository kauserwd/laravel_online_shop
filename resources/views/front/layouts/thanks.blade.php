@extends('front.layouts.app')

@section('content')

    <section class="container ">
        <div class="col-md-12 text-center py-5">
                @if(Session::has('success'))
                    <div class="alert alert-success">
                        {{ Session::get('success') }}
                    </div>
                @endif

            <h4> Thank You!</h4>
            <p>Your Order Id is: {{ $id }}</p>
        </div>
    </section>

@endsection

