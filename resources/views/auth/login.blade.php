<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CCSL Research Institute - Login</title>
<link rel="icon" type="image/png" href="{{ asset('assets/logo_prscope.png') }}" />
<style>
  :root{
    --navy-900:#28344d;
    --navy-800:#2f3e5c;
    --navy-700:#3d5177;
    --navy-600:#4a5f88;
    --navy-accent:#5b76a8;
    --ink:#1e293b;
    --muted:#64748b;
    --line:#cbd5e1;
    --bg-page:#f8fafc;
    --white:#ffffff;
  }

  *{box-sizing:border-box;}

  body{
    margin:0;
    font-family:'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
    background: var(--bg-page);
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
  }

  .card{
    width:100%;
    max-width:460px;
    background:var(--white);
    border-radius:12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03), 0 20px 25px -5px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
  }

  .logo-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 36px;
  }

  .logo-row img {
    height: 60px;
    object-fit: contain;
  }

  .logo-text {
    display: flex;
    flex-direction: column;
  }

  .logo-text .name {
    font-size: 24px;
    font-weight: 800;
    line-height: 1.1;
    color: var(--ink);
  }

  .logo-text .sub {
    font-size: 11.5px;
    letter-spacing: 1.5px;
    font-weight: 700;
    color: var(--muted);
    margin-top: 3px;
    text-transform: uppercase;
  }

  .form-container {
    width: 100%;
  }

  .field {
    margin-bottom: 20px;
  }

  .field label {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 8px;
  }

  .input-wrap {
    position: relative;
  }

  .input-wrap svg.field-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    stroke: var(--muted);
    fill: none;
    stroke-width: 1.8;
    pointer-events: none;
  }

  .input-wrap input {
    width: 100%;
    padding: 13px 14px 13px 44px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14.5px;
    color: var(--ink);
    background: #f8fafc;
    outline: none;
    transition: border-color 0.15s, background-color 0.15s;
  }

  .input-wrap input:focus {
    border-color: var(--navy-600);
    background: #ffffff;
  }

  .toggle-eye {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    stroke: var(--muted);
    fill: none;
    stroke-width: 1.8;
    cursor: pointer;
  }

  .row-between {
    display: flex;
    align-items: center;
    margin-bottom: 24px;
    padding-left: 2px;
  }

  .remember {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    color: #475569;
    cursor: pointer;
  }

  .remember input {
    accent-color: var(--navy-800);
    width: 15px;
    height: 15px;
    cursor: pointer;
    margin: 0;
  }

  .btn-signin {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 8px;
    background: var(--navy-800);
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.15s, transform 0.1s;
    text-align: center;
  }

  .btn-signin:hover {
    background-color: var(--navy-900);
  }

  .btn-signin:active {
    transform: scale(0.99);
  }

  .error-text {
    color: #ef4444;
    font-size: 12px;
    margin-top: 6px;
    font-weight: 500;
  }
</style>
</head>
<body>

  <div class="card">

    <div class="logo-row">
      <img src="{{ asset('assets/logo_riccsl.png') }}" alt="CCSL Research Institute">
      <div class="logo-text">
        <div class="name">CCSL</div>
        <div class="sub">RESEARCH INSTITUTE</div>
      </div>
    </div>

    <form action="{{ route('login.post') }}" method="POST" class="form-container">
      @csrf
      
      <div class="field">
        <label for="username">Username <span style="color:#ef4444">*</span></label>
        <div class="input-wrap">
          <svg class="field-icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
          <input type="text" name="username" id="username" placeholder="Enter your username" value="{{ old('username') }}" required>
        </div>
        @error('username')
          <div class="error-text">{{ $message }}</div>
        @enderror
      </div>

      <div class="field">
        <label for="password">Password <span style="color:#ef4444">*</span></label>
        <div class="input-wrap">
          <svg class="field-icon" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
          <input type="password" name="password" id="password" placeholder="Enter your password" required>
          <svg class="toggle-eye" onclick="togglePw()" viewBox="0 0 24 24" id="eyeIcon"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        @error('password')
          <div class="error-text">{{ $message }}</div>
        @enderror
      </div>

      <div class="row-between">
        <label class="remember" for="remember">
          <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
          Remember me
        </label>
      </div>

      <button type="submit" class="btn-signin">Login</button>
    </form>

  </div>

<script>
function togglePw(){
  const pw = document.getElementById('password');
  pw.type = pw.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
