@extends('layout')

<!-- what goes as the title of the page-->
@section('title', 'Login')  


<!--what goes into the section/body of this page -->
@section('content')
<div class="container">
    <div class="form">
        <h2>Log Into Your Account</h2>
        <form action="{{ asset('/login') }}" method="post">
            @csrf
            <div class="mb-3">
                <label for="useremail" class="form-label">Email address</label>
                <input type="email" class="form-control lg:w-auto" name="useremail"  placeholder="Enter email">
            </div>
            <div class="mb-3">
                <label for="userpassword" class="form-label">Password</label>
                <input type="password" class="form-control" name="userpassword" placeholder="Enter your Password">
            </div>
            <button class="btn btn-primary" type="submit">Sign In</button>
        </form>
        <p>Don't have an account? <a href="/signup">Sign up</a></p>
    </div>
</div>
@endsection

