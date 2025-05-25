<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কি মামা চলে আসছো?</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kalpurush&display=swap');
        .bengali-font {
            font-family: 'Kalpurush', sans-serif;
        }

        .gun-man {
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .text-glow {
            text-shadow: 0 0 5px rgba(239, 68, 68, 0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 5px rgba(239, 68, 68, 0.3);
            }
            to {
                text-shadow: 0 0 10px rgba(239, 68, 68, 0.5), 0 0 15px rgba(239, 68, 68, 0.3);
            }
        }

        .warning-blink {
            animation: blink 1s linear infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        .gun-fire {
            animation: fire 1.5s infinite;
        }

        @keyframes fire {
            0% { opacity: 0; transform: translateX(0) scale(0.5); }
            20% { opacity: 1; transform: translateX(10px) scale(1); }
            40% { opacity: 0.8; transform: translateX(20px) scale(1.2); }
            60% { opacity: 0.6; transform: translateX(30px) scale(1); }
            80% { opacity: 0.3; transform: translateX(40px) scale(0.8); }
            100% { opacity: 0; transform: translateX(50px) scale(0.5); }
        }

        .muzzle-flash {
            animation: flash 1.5s infinite;
        }

        @keyframes flash {
            0%, 90%, 100% { opacity: 0; }
            5%, 15% { opacity: 1; }
        }
    </style>
</head>
<body class="bg-black min-h-screen flex items-center justify-center overflow-hidden relative">
<!-- Animated background -->
<div class="absolute inset-0 bg-gradient-to-br from-red-900 via-black to-yellow-900 opacity-50"></div>

<!-- Warning stripes -->
<div class="absolute top-0 left-0 w-full h-8 bg-gradient-to-r from-yellow-400 via-red-500 to-yellow-400 warning-blink"></div>
<div class="absolute bottom-0 left-0 w-full h-8 bg-gradient-to-r from-yellow-400 via-red-500 to-yellow-400 warning-blink"></div>

<!-- Main content container -->
<div class="relative z-10 text-center px-8 max-w-4xl mx-auto">

    <!-- Gun man illustration with fire effects -->
    <div class="gun-man mb-8 relative">
        <svg class="w-48 h-48 mx-auto text-yellow-400" fill="currentColor" viewBox="0 0 100 100">
            <!-- Head -->
            <circle cx="50" cy="20" r="8" class="text-yellow-200" fill="currentColor"/>
            <!-- Hat -->
            <rect x="42" y="10" width="16" height="6" rx="2" class="text-gray-800" fill="currentColor"/>
            <!-- Eyes -->
            <circle cx="47" cy="18" r="1" class="text-black" fill="currentColor"/>
            <circle cx="53" cy="18" r="1" class="text-black" fill="currentColor"/>
            <!-- Mustache -->
            <ellipse cx="50" cy="23" rx="3" ry="1" class="text-black" fill="currentColor"/>

            <!-- Body -->
            <rect x="45" y="28" width="10" height="25" rx="2" class="text-blue-600" fill="currentColor"/>

            <!-- Arms -->
            <rect x="35" y="32" width="8" height="4" rx="2" class="text-yellow-200" fill="currentColor"/>
            <rect x="57" y="32" width="8" height="4" rx="2" class="text-yellow-200" fill="currentColor"/>

            <!-- Gun -->
            <rect x="66" y="30" width="15" height="3" rx="1" class="text-gray-700" fill="currentColor"/>
            <rect x="79" y="28" width="4" height="7" rx="1" class="text-gray-700" fill="currentColor"/>

            <!-- Legs -->
            <rect x="46" y="53" width="3" height="15" rx="1" class="text-blue-800" fill="currentColor"/>
            <rect x="51" y="53" width="3" height="15" rx="1" class="text-blue-800" fill="currentColor"/>

            <!-- Feet -->
            <ellipse cx="47" cy="70" rx="4" ry="2" class="text-black" fill="currentColor"/>
            <ellipse cx="53" cy="70" rx="4" ry="2" class="text-black" fill="currentColor"/>

            <!-- Angry eyebrows -->
            <rect x="46" y="15" width="4" height="1" rx="0.5" class="text-red-600" fill="currentColor" transform="rotate(-20 48 15.5)"/>
            <rect x="50" y="15" width="4" height="1" rx="0.5" class="text-red-600" fill="currentColor" transform="rotate(20 52 15.5)"/>
        </svg>

        <!-- Gun fire animation -->
        <div class="absolute top-1/2 right-8 gun-fire text-orange-500 text-2xl">💥</div>
        <div class="absolute top-1/2 right-12 muzzle-flash text-yellow-300 text-xl">🔥</div>
        <div class="absolute top-1/2 right-4 gun-fire text-red-500 text-lg" style="animation-delay: 0.3s;">💨</div>
    </div>

    <!-- Main Bengali text -->
    <h1 class="bengali-font text-6xl md:text-8xl font-bold text-red-500 text-glow mb-8 transform hover:scale-110 transition-transform duration-300 cursor-pointer">
        কি মামা চলে আসছো?
    </h1>

    <!-- Subtitle -->
    <p class="bengali-font text-yellow-400 text-xl md:text-2xl font-bold mb-8 opacity-90">
        [ হুদাই ধরাটা খাইলা মামা! 🕵️‍♂️ ]
    </p>

    <!-- Warning message -->
    <div class="bg-red-600 border-4 border-yellow-400 rounded-lg p-6 mb-8 transform rotate-1 hover:rotate-0 transition-transform duration-300">
        <p class="bengali-font text-white font-bold text-lg">
            ⚠️ আরে থাম! ওইখানেই দাঁড়া! ⚠️
        </p>
        <p class="bengali-font text-yellow-200 mt-2">
            এইটা আমার এলাকা! চুপি চুপি আর আগাস না? গুলি করমু এহন! 💥
        </p>
    </div>

    <!-- Additional funny Bengali content -->
    <div class="bengali-font bg-yellow-900 border-2 border-yellow-500 rounded-lg p-4 mb-6 mx-auto max-w-md">
        <p class="text-yellow-100 font-bold text-center">
            📢 জরুরি ঘোষণা 📢
        </p>
        <p class="text-yellow-200 mt-2 text-sm">
            আরে ভাইজান, লুকায় লুকায় আসো ক্যান? <br>
            এইখানে তো ভূতের বাড়ি! 👻 <br>
            পান সুপারি খাইয়া বাড়ি যাও! 🍃
        </p>
    </div>

    <!-- Funny warning signs -->
    <div class="bengali-font grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 max-w-2xl mx-auto">
        <div class="bg-red-500 text-white p-3 rounded-lg text-center transform hover:scale-105 transition-transform">
            <div class="text-2xl mb-1">🚫</div>
            <div class="text-xs font-bold">চোরামি বন্ধ</div>
        </div>
        <div class="bg-orange-500 text-white p-3 rounded-lg text-center transform hover:scale-105 transition-transform">
            <div class="text-2xl mb-1">👮</div>
            <div class="text-xs font-bold">দারোয়ান আছে</div>
        </div>
        <div class="bg-purple-500 text-white p-3 rounded-lg text-center transform hover:scale-105 transition-transform">
            <div class="text-2xl mb-1">🔒</div>
            <div class="text-xs font-bold">তালা বন্ধ</div>
        </div>
    </div>

    <!-- Footer message -->
    <div class="bengali-font mt-12 text-gray-400 text-sm">
        <p>দেশি ডাকাত ভার্সন ৩.০</p>
        <p class="mt-2">বাঙালি টেকনোলজি™ দ্বারা চালিত</p>
    </div>
</div>

<!-- Corner decorations -->
<div class="absolute top-4 left-4 text-red-500 text-2xl animate-spin">⚠️</div>
<div class="absolute top-4 right-4 text-red-500 text-2xl animate-spin" style="animation-direction: reverse;">⚠️</div>
<div class="absolute bottom-4 left-4 text-yellow-400 text-2xl animate-bounce">🚨</div>
<div class="absolute bottom-4 right-4 text-yellow-400 text-2xl animate-bounce" style="animation-delay: 0.5s;">🚨</div>
</body>
</html>
