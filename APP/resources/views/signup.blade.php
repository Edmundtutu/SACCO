@extends('layout')

<!-- what goes as the title of the page-->
@section('title', 'Signup')  


<!--what goes into the section/body of this page -->
@section('content')
<div class="container">
    
    <div class="form">
        <h2>Register for a New Account</h2>
        <form action="/signup" method="post">
            @csrf
            <div class="mb-3">
                <label for="registeredname" class="form-label">User Name</label>
                <input type="text" class="form-control" name="registeredname" placeholder="Enter your name">
            </div>
            <div class="mb-3">
                <label for="registeredemail" class="form-label">Email address</label>
                <input type="email" class="form-control" name="registeredemail" placeholder="Enter email">
            </div>
            <div class="mb-3">
                <label for="registeredpassword" class="form-label">Password</label>
                <input type="password" class="form-control" name="userregistered" placeholder="Enter your Password">
            </div>
            <button class="btn btn-primary" type="submit">Sign up</button>
        </form>
        <p>Already have an account? <a href="/login">Sign in</a></p>
    </div>


</div>


@endsection

