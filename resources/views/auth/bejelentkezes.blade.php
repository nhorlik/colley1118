@extends('layout')
@section('cim', 'Bejelentkezés')
@section('tartalom')
    <div class="auth-doboz">
        <h1>Bejelentkezés</h1>
        <form method="POST" action="{{ route('bejelentkezes.kuld') }}" class="auth-forma">
            @csrf
            <label>
                E-mail cím
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label>
                Jelszó
                <input type="password" name="jelszo" required>
            </label>
            <label class="checkbox-sor">
                <input type="checkbox" name="maradjon_bejelentkezve" value="1">
                Maradjak bejelentkezve
            </label>
            <button type="submit" class="gomb gomb-elsodleges">Bejelentkezés</button>
        </form>
        <p class="sugo">Még nincs fiókod? <a href="{{ route('regisztracio') }}">Regisztrálj itt.</a></p>
    </div>
@endsection
