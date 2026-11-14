<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to MartPoint</title>
    <link rel="shortcut icon" href="<?= base_url('setup/assets/martpoint-icon.png?v=2') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; position: relative;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; }
        }

        /* Floating orbs */
        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(80px); opacity: 0.35; pointer-events: none; z-index: 0;
        }
        .orb-1 { width: 400px; height: 400px; background: radial-gradient(circle, #6366f1 0%, transparent 70%); top: -10%; left: -10%; animation: floatOrb1 20s ease-in-out infinite; }
        .orb-2 { width: 300px; height: 300px; background: radial-gradient(circle, #10b981 0%, transparent 70%); bottom: -5%; right: -5%; animation: floatOrb2 18s ease-in-out infinite; }
        .orb-3 { width: 200px; height: 200px; background: radial-gradient(circle, #f59e0b 0%, transparent 70%); top: 40%; left: 60%; animation: floatOrb3 25s ease-in-out infinite; }
        @keyframes floatOrb1 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(60px,40px) scale(1.1);} }
        @keyframes floatOrb2 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-50px,-30px) scale(1.15);} }
        @keyframes floatOrb3 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-30px,50px) scale(0.9);} }

        /* Confetti / particle system */
        .particle {
            position: fixed; pointer-events: none; z-index: 5;
            animation: particleFall linear forwards;
        }
        @keyframes particleFall {
            0% { transform: translateY(-10vh) rotate(0deg) scale(1); opacity: 1; }
            100% { transform: translateY(110vh) rotate(720deg) scale(0.5); opacity: 0; }
        }

        /* Welcome card */
        .welcome-card {
            position: relative; z-index: 10;
            text-align: center;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05) inset;
            padding: 52px 44px;
            max-width: 480px; width: 100%;
            margin: 24px;
            opacity: 0; transform: translateY(30px) scale(0.96);
            animation: cardReveal 1s cubic-bezier(0.16, 1, 0.3, 1) 0.3s forwards;
        }
        @keyframes cardReveal { to { opacity: 1; transform: translateY(0) scale(1); } }

        .success-ring {
            width: 90px; height: 90px; border-radius: 50%;
            margin: 0 auto 24px;
            background: rgba(16,185,129,0.12);
            border: 2px solid rgba(16,185,129,0.3);
            display: flex; align-items: center; justify-content: center;
            position: relative;
            animation: ringPop 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.6s both;
        }
        .success-ring::before {
            content: ''; position: absolute; inset: -6px;
            border-radius: 50%;
            border: 2px solid rgba(16,185,129,0.15);
            animation: ringPulse 2s ease infinite 1s;
        }
        @keyframes ringPop { 0%{transform:scale(0);} 100%{transform:scale(1);} }
        @keyframes ringPulse { 0%,100%{transform:scale(1);opacity:1;} 50%{transform:scale(1.08);opacity:0.5;} }

        .success-ring svg { width: 40px; height: 40px; color: #34d399; }

        .welcome-card h1 {
            font-size: 26px; font-weight: 800; color: #f8fafc;
            margin: 0 0 8px;
            animation: fadeUp 0.6s ease 0.9s both;
        }
        .welcome-card .subtitle {
            font-size: 15px; color: #94a3b8; margin: 0 0 28px;
            animation: fadeUp 0.6s ease 1.0s both;
        }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        .feature-list {
            text-align: left; margin-bottom: 32px;
            animation: fadeUp 0.6s ease 1.1s both;
        }
        .feature-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 10px;
            margin-bottom: 6px;
            font-size: 13px; color: #cbd5e1;
            background: rgba(255,255,255,0.03);
        }
        .feature-item:last-child { margin-bottom: 0; }
        .feature-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .feature-dot.indigo { background: #6366f1; box-shadow: 0 0 8px rgba(99,102,241,0.4); }
        .feature-dot.emerald { background: #10b981; box-shadow: 0 0 8px rgba(16,185,129,0.4); }
        .feature-dot.amber { background: #f59e0b; box-shadow: 0 0 8px rgba(245,158,11,0.4); }

        .btn-login {
            display: inline-block; width: 100%; padding: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff; border: none; border-radius: 14px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(99,102,241,0.35);
            transition: all 0.3s ease;
            animation: fadeUp 0.6s ease 1.2s both;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(99,102,241,0.45); }

        .login-note {
            margin-top: 16px; font-size: 12px; color: #64748b;
            animation: fadeUp 0.6s ease 1.3s both;
        }
        .login-note code {
            background: rgba(255,255,255,0.06); padding: 2px 8px; border-radius: 6px;
            font-family: 'SF Mono', monospace; font-size: 12px; color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="welcome-card">
        <div class="success-ring">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h1>Welcome to MartPoint!</h1>
        <p class="subtitle">Your retail system is installed and ready to go.</p>

        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-dot indigo"></div>
                <span>Database connected and tables created</span>
            </div>
            <div class="feature-item">
                <div class="feature-dot emerald"></div>
                <span>Default data seeded successfully</span>
            </div>
            <div class="feature-item">
                <div class="feature-dot amber"></div>
                <span>Configuration files written</span>
            </div>
        </div>

        <a href="<?= base_url('login') ?>" class="btn-login">Log In to MartPoint</a>

    </div>

    <script>
        (function() {
            const colours = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'];
            const shapes = ['circle', 'square', 'triangle'];

            function createParticle() {
                const p = document.createElement('div');
                p.className = 'particle';
                const size = Math.random() * 8 + 4;
                const left = Math.random() * 100;
                const duration = Math.random() * 4 + 4;
                const delay = Math.random() * 2;
                const colour = colours[Math.floor(Math.random() * colours.length)];
                const shape = shapes[Math.floor(Math.random() * shapes.length)];

                p.style.cssText = `
                    left: ${left}vw;
                    width: ${size}px; height: ${size}px;
                    background: ${colour};
                    opacity: ${Math.random() * 0.5 + 0.3};
                    animation-duration: ${duration}s;
                    animation-delay: ${delay}s;
                `;

                if (shape === 'circle') {
                    p.style.borderRadius = '50%';
                } else if (shape === 'triangle') {
                    p.style.width = '0'; p.style.height = '0';
                    p.style.background = 'transparent';
                    p.style.borderLeft = `${size/2}px solid transparent`;
                    p.style.borderRight = `${size/2}px solid transparent`;
                    p.style.borderBottom = `${size}px solid ${colour}`;
                }

                document.body.appendChild(p);
                setTimeout(function() { p.remove(); }, (duration + delay) * 1000);
            }

            // Burst on load
            for (var i = 0; i < 40; i++) createParticle();
            // Continuous gentle rain
            setInterval(createParticle, 300);
        })();
    </script>
</body>
</html>
