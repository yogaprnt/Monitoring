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
    --line:#e6e9f0;
    --bg-page:#eef1f6;
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
    font-family:'Segoe UI', Arial, sans-serif;
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
  /* dynamic shapes style */

  .card{
    position:relative;
    z-index:1;
    display:flex;
    width:100%;
    max-width:920px;
    min-height:480px;
    background:var(--white);
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 30px 60px rgba(15,23,42,0.35);
  }

  /* Left panel: keeps the deep slate-blue identity + illustration feel of Image 1,
     but now carries the intro copy + feature list structure of Image 2 */
  .panel-left{
    flex:1 1 46%;
    background: linear-gradient(160deg, var(--navy-800), var(--navy-900));
    color:var(--white);
    padding:48px 40px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    position:relative;
  }

  .brand-tag{
    font-size:13px;
    letter-spacing:2px;
    text-transform:uppercase;
    color:rgba(255,255,255,0.6);
    margin-bottom:14px;
  }

  .panel-left h1{
    font-size:24px;
    line-height:1.35;
    margin:0 0 16px;
    font-weight:700;
  }

  .panel-left p{
    font-size:14.5px;
    line-height:1.7;
    color:rgba(255,255,255,0.78);
    margin:0 0 32px;
    max-width:340px;
  }

  .feature-list{
    list-style:none;
    margin:0;
    padding:0;
    display:flex;
    flex-direction:column;
    gap:18px;
  }

  .feature-list li{
    display:flex;
    align-items:center;
    gap:14px;
    font-size:14px;
    font-weight:600;
  }

  .feature-icon{
    width:36px;
    height:36px;
    border-radius:50%;
    background:rgba(255,255,255,0.12);
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
  }
  .feature-icon svg{width:17px;height:17px;fill:none;stroke:#fff;stroke-width:2;}

  /* Right panel: login form, kept clean/white like Image 1 */
  .panel-right{
    flex:1 1 54%;
    padding:48px 52px;
    display:flex;
    flex-direction:column;
    justify-content:center;
  }

  .logo-mark{
    width:52px;
    height:52px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    overflow:hidden;
  }
  .logo-mark img{
    width:100%;
    height:100%;
    object-fit:cover;
  }
  .logo-row{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:8px;
  }

  .logo-text{
    line-height:1.2;
  }
  .logo-text .name{
    font-weight:800;
    font-size:16px;
    color:var(--ink);
    letter-spacing:0.5px;
  }
  .logo-text .sub{
    font-size:10.5px;
    letter-spacing:1.5px;
    color:var(--muted);
    text-transform:uppercase;
  }

  .form-heading{
    margin:34px 0 4px;
    font-size:24px;
    font-weight:700;
    color:var(--ink);
  }
  .form-sub{
    margin:0 0 30px;
    font-size:13.5px;
    color:var(--muted);
  }

  .field{
    margin-bottom:20px;
  }
  .field label{
    display:block;
    font-size:13.5px;
    font-weight:600;
    color:var(--ink);
    margin-bottom:8px;
  }
  .input-wrap{
    position:relative;
  }
  .input-wrap svg{
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    width:16px;
    height:16px;
    stroke:var(--muted);
    fill:none;
    stroke-width:1.8;
  }
  .input-wrap input{
    width:100%;
    padding:13px 14px 13px 40px;
    border:1px solid var(--line);
    border-radius:8px;
    font-size:14px;
    color:var(--ink);
    background:#fafbfc;
    outline:none;
    transition:border-color .15s, box-shadow .15s;
  }
  .input-wrap input:focus{
    border-color:var(--navy-600);
    box-shadow:0 0 0 3px rgba(74,95,136,0.12);
    background:#fff;
  }
  .toggle-eye{
    left:auto;
    right:14px;
    cursor:pointer;
    pointer-events:auto;
  }

  .row-between{
    display:flex;
    align-items:center;
    justify-content:flex-start;
    margin-bottom:26px;
    padding-left: 2px;
  }
  .remember{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:13.5px;
    color:var(--ink);
    cursor:pointer;
  }
  .remember input{accent-color:var(--navy-700);width:15px;height:15px;cursor:pointer;margin:0;}
  .forgot{
    font-size:13px;
    color:var(--navy-700);
    text-decoration:none;
    font-weight:600;
  }
  .forgot:hover{text-decoration:underline;}

  .btn-signin{
    width:100%;
    padding:14px;
    border:none;
    border-radius:8px;
    background: linear-gradient(135deg, var(--navy-700), var(--navy-900));
    color:#fff;
    font-size:15px;
    font-weight:700;
    letter-spacing:0.3px;
    cursor:pointer;
    transition:opacity .15s, transform .15s;
  }
  .btn-signin:hover{opacity:0.92;transform:translateY(-1px);}

  .error-text{
    color:#ef4444;
    font-size:12px;
    margin-top:6px;
    font-weight:500;
  }

  @media (max-width:760px){
    .card{flex-direction:column;max-width:420px;}
    .panel-left{padding:36px 30px;}
    .panel-right{padding:36px 30px;}
  }
</style>
</head>
<body>

  <!-- Dynamic floating background shapes container -->
  <div id="shapes-container"></div>

  <div class="card">

    <!-- LEFT: identity + feature highlights -->
    <div class="panel-left">
      <h1>Dashboard Monitoring Kinerja Non-Finansial Center of Excellence di Lingkungan RI-CCSL</h1>
      <p>Pantau, kelola, dan evaluasi kinerja non-finansial Center of Excellence secara terpusat dan real-time di lingkungan RI-CCSL.</p>

      <ul class="feature-list">
        <li>
          <span class="feature-icon" style="font-size: 16px;">✍️</span>
          Input capaian kinerja dengan mudah
        </li>
        <li>
          <span class="feature-icon" style="font-size: 16px;">✅</span>
          Proses verifikasi data yang terstruktur
        </li>
        <li>
          <span class="feature-icon" style="font-size: 16px;">📊</span>
          Monitoring progres setiap unit kerja
        </li>
      </ul>
    </div>

    <!-- RIGHT: login form -->
    <div class="panel-right">
      <div class="logo-row" style="margin-bottom: 30px;">
        <img src="{{ asset('assets/logo_riccsl.png') }}" alt="CCSL Research Institute" style="height: 90px; object-fit: contain;">
        <div class="logo-text">
          <div class="name" style="font-size: 26px; font-weight: 800; line-height: 1.1;">CCSL</div>
          <div class="sub" style="font-size: 13px; letter-spacing: 2px; font-weight: 700; color: var(--muted); margin-top: 4px;">RESEARCH INSTITUTE</div>
        </div>
      </div>

      <form action="{{ route('login.post') }}" method="POST">
        @csrf
        
        <div class="field">
          <label for="username">Username <span style="color:#ef4444">*</span></label>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
            <input type="text" name="username" id="username" placeholder="Enter your username" value="{{ old('username') }}" required>
          </div>
          @error('username')
            <div class="error-text">{{ $message }}</div>
          @enderror
        </div>

        <div class="field">
          <label for="password">Password <span style="color:#ef4444">*</span></label>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
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
