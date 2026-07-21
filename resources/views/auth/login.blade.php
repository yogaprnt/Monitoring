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
    --navy-800:#324467;
    --navy-700:#3d5177;
    --navy-600:#4a5f88;
    --navy-accent:#5b76a8;
    --ink:#1f2733;
    --muted:#7a8496;
    --line:#cbd5e1;
    --bg-page:#f8fafc;
    --white:#ffffff;
  }

  *{box-sizing:border-box;}

  @keyframes gradientBg {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  @keyframes floatSlow1 {
    0% { transform: translateY(0) rotate(0deg) scale(1); }
    50% { transform: translateY(-20px) rotate(180deg) scale(1.05); }
    100% { transform: translateY(0) rotate(360deg) scale(1); }
  }

  @keyframes floatSlow2 {
    0% { transform: translateY(0) rotate(0deg) scale(1); }
    50% { transform: translateY(20px) rotate(-180deg) scale(0.95); }
    100% { transform: translateY(0) rotate(-360deg) scale(1); }
  }

  body{
    margin:0;
    font-family:'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
    background: linear-gradient(-45deg, var(--navy-900), #1e293b, var(--navy-700), var(--navy-800));
    background-size: 400% 400%;
    animation: gradientBg 15s ease infinite;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
    overflow:hidden;
  }

  /* decorative floating shapes */
  .bg-shape{
    position:fixed;
    pointer-events:none;
    z-index: 0;
  }

  .card{
    position:relative;
    z-index:1;
    width:100%;
    max-width:420px;
    background:var(--white);
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 30px 60px rgba(15,23,42,0.35);
    padding: 44px 36px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .logo-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 30px;
    text-align: center;
  }

  .logo-container img {
    height: 75px;
    object-fit: contain;
    margin-bottom: 16px;
  }

  .login-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--ink);
    margin: 0 0 6px 0;
  }

  .login-subtitle {
    font-size: 12px;
    color: var(--muted);
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
  }

  .form-container {
    width: 100%;
  }

  /* Stacked input fields */
  .input-group {
    border: 1px solid var(--line);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 20px;
    width: 100%;
  }

  .input-field {
    position: relative;
    background: #fafbfc;
  }

  .input-field:first-child {
    border-bottom: 1px solid var(--line);
  }

  .input-field svg.field-icon {
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

  .input-field input {
    width: 100%;
    padding: 15px 14px 15px 44px;
    border: none;
    font-size: 14.5px;
    color: var(--ink);
    background: transparent;
    outline: none;
    transition: background-color .15s;
  }

  .input-field input:focus {
    background-color: #ffffff;
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

  .row-between{
    display:flex;
    align-items:center;
    justify-content:flex-start;
    margin-bottom:24px;
    padding-left: 2px;
    width: 100%;
  }

  .remember{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13.5px;
    color:var(--ink);
    cursor:pointer;
  }

  .remember input{
    accent-color:var(--navy-700);
    width:15px;
    height:15px;
    cursor:pointer;
    margin:0;
  }

  .btn-signin{
    width:100%;
    padding: 14px;
    border:none;
    border-radius:8px;
    background: #0470D4;
    color:#fff;
    font-size:15px;
    font-weight:700;
    letter-spacing:0.3px;
    cursor:pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background-color .15s, transform .15s;
  }

  .btn-signin:hover{
    background-color: #035eb3;
    transform:translateY(-1px);
  }

  .btn-signin svg {
    flex-shrink: 0;
  }

  .error-text{
    color:#ef4444;
    font-size:12px;
    margin-top:4px;
    font-weight:500;
    padding: 0 4px;
  }

  .footer-text {
    margin-top: 28px;
    font-size: 13.5px;
    color: var(--muted);
    text-align: center;
    width: 100%;
  }

  @media (max-width:480px){
    .card{padding:36px 24px;}
  }
</style>
</head>
<body>

  <!-- Dynamic floating background shapes container -->
  <div id="shapes-container"></div>

  <div class="card">

    <div class="logo-container">
      <img src="{{ asset('assets/logo_riccsl.png') }}" alt="CCSL Research Institute">
      <h2 class="login-title">Login ke Monitoring Kinerja</h2>
      <p class="login-subtitle">CCSL Research Institute</p>
    </div>

    <form action="{{ route('login.post') }}" method="POST" class="form-container">
      @csrf
      
      <div class="input-group">
        <div class="input-field">
          <svg class="field-icon" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
          <input type="text" name="username" id="username" placeholder="Username" value="{{ old('username') }}" required>
        </div>

        <div class="input-field">
          <svg class="field-icon" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
          <input type="password" name="password" id="password" placeholder="Password" required>
          <svg class="toggle-eye" onclick="togglePw()" viewBox="0 0 24 24" id="eyeIcon"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
      </div>

      @error('username')
        <div class="error-text" style="margin-top: -12px; margin-bottom: 12px;">{{ $message }}</div>
      @enderror
      @error('password')
        <div class="error-text" style="margin-top: -12px; margin-bottom: 12px;">{{ $message }}</div>
      @enderror

      <div class="row-between">
        <label class="remember" for="remember">
          <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
          Remember me
        </label>
      </div>

      <button type="submit" class="btn-signin">
        <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        Login
      </button>
    </form>

    <div class="footer-text">
      Belum punya akun? Hubungi admin.
    </div>

  </div>

<script>
function togglePw(){
  const pw = document.getElementById('password');
  pw.type = pw.type === 'password' ? 'text' : 'password';
}

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('shapes-container') || document.body;
  const shapesCount = 45; // 45 beautiful floating shapes!
  
  for (let i = 0; i < shapesCount; i++) {
    const shape = document.createElement('div');
    shape.className = 'bg-shape';
    
    // Random size between 10px and 240px
    const size = Math.floor(Math.random() * 230) + 10;
    shape.style.width = size + 'px';
    shape.style.height = size + 'px';
    
    // Random position
    shape.style.left = Math.floor(Math.random() * 100) + '%';
    shape.style.top = Math.floor(Math.random() * 100) + '%';
    
    // Random border radius (either circle, square, or rounded square)
    const shapeType = Math.floor(Math.random() * 3);
    if (shapeType === 0) {
      shape.style.borderRadius = '50%';
    } else if (shapeType === 1) {
      shape.style.borderRadius = '16px';
    } else {
      shape.style.borderRadius = '0px';
    }
    
    // Random styling: border, dashed, or radial gradient glow
    const styleType = Math.floor(Math.random() * 3);
    if (styleType === 0) {
      shape.style.border = '2px solid rgba(255, 255, 255, 0.04)';
      shape.style.background = 'rgba(255, 255, 255, 0.005)';
    } else if (styleType === 1) {
      shape.style.border = '1.5px dashed rgba(255, 255, 255, 0.03)';
      shape.style.borderRadius = '50%';
    } else {
      shape.style.background = 'radial-gradient(circle, rgba(255,255,255,' + (Math.random() * 0.04 + 0.02).toFixed(3) + ') 0%, transparent 70%)';
    }
    
    // Random animation (floatSlow1 or floatSlow2)
    const animName = Math.random() > 0.5 ? 'floatSlow1' : 'floatSlow2';
    const duration = Math.floor(Math.random() * 20) + 15; // 15s to 35s
    const delay = -Math.floor(Math.random() * 20); // negative delay to distribute positions immediately
    
    shape.style.animation = animName + ' ' + duration + 's infinite linear';
    shape.style.animationDelay = delay + 's';
    
    container.appendChild(shape);
  }
});
</script>
</body>
</html>
