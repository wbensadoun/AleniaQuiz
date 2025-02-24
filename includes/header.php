<header class="alenia-header">
    <div class="alenia-brand">
        <div class="logo-container">
            <h1>ALENIA</h1>
            <span class="tagline">Excellence in Learning</span>
        </div>
    </div>
</header>

<style>
.alenia-header {
    background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
    position: relative;
    overflow: hidden;
    width: 100%;
    display: block;
}

.alenia-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, 
        rgba(255,255,255,0.05) 25%, 
        transparent 25%, 
        transparent 50%, 
        rgba(255,255,255,0.05) 50%, 
        rgba(255,255,255,0.05) 75%, 
        transparent 75%, 
        transparent);
    background-size: 100px 100px;
    animation: moveBackground 15s linear infinite;
}

@keyframes moveBackground {
    0% {
        background-position: 0 0;
    }
    100% {
        background-position: 100px 100px;
    }
}

.alenia-brand {
    position: relative;
    z-index: 1;
    width: 100%;
}

.logo-container {
    display: inline-block;
    padding: 0.5rem 2rem;
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    background: rgba(255,255,255,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.logo-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.alenia-brand h1 {
    font-size: 4rem;
    margin: 0;
    font-weight: 600;
    letter-spacing: 8px;
    font-family: 'Montserrat', sans-serif;
    background: linear-gradient(to right, #ffffff 0%, #e6e6e6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.tagline {
    display: block;
    font-size: 1.2rem;
    color: rgba(255,255,255,0.9);
    margin-top: 0.5rem;
    font-weight: 300;
    letter-spacing: 2px;
    text-transform: uppercase;
}

/* Animation au survol */
.logo-container {
    position: relative;
    overflow: hidden;
}

.logo-container::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
    transform: scale(0);
    opacity: 0;
    transition: transform 0.6s ease-out, opacity 0.6s ease-out;
}

.logo-container:hover::after {
    transform: scale(1);
    opacity: 1;
}

/* Reset du body pour le header */
body {
    margin: 0;
    padding: 0;
}

/* Container modifications */
.container {
    padding-top: 20px;
}
</style>
