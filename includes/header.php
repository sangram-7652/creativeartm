<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Creative Art Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900">

    <!-- Header -->
    <header class="flex items-center flex-col lg:flex-row  justify-between px-6 py-4 bg-white shadow-md">
        <div class="text-2xl font-bold text-indigo-600">
            <a href="#">CreativeArtM</a>
        </div>
        <nav class="hidden md:block">
            <ul class="flex space-x-6 text-gray-700 font-medium">
                <li><a href="#" class="hover:text-indigo-600">Home</a></li>
                <li><a href="#" class="hover:text-indigo-600">About</a></li>
                <li><a href="#" class="hover:text-indigo-600">Services</a></li>
                <li><a href="#" class="hover:text-indigo-600">Products</a></li>
            </ul>
        </nav>
        <div class="space-x-4 mt-4 lg:mt-0">
            <a href="login.php" class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600 transition">Login</a>
            <a href="signup.php" class="px-4 py-2 border border-indigo-500 text-indigo-500 rounded hover:bg-indigo-50 transition">Signup</a>
            <a href="/creativeartm/admin/login.php" target="_blank" class="text-xl border-2 shadow p-2 rounded-full">👨‍💼</a>
        </div>
    </header>

    <!-- Modern Banner -->
    <section class="relative h-[80vh] bg-gradient-to-br from-indigo-700 via-purple-700 to-pink-600 overflow-hidden flex items-center justify-center">

        <!-- Decorative Background (optional abstract effect) -->
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] bg-cover bg-center"></div>

        <!-- Glassmorphism Content Box -->
        <div class="relative bg-white/10 backdrop-blur-lg text-white p-10 rounded-xl shadow-lg max-w-xl text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4 drop-shadow">Welcome to Creative Art Management</h1>
            <p class="text-lg md:text-xl mb-6">Manage. Create. Inspire. All in one platform.</p>
            <button class="px-6 py-3 bg-white text-indigo-700 font-semibold rounded-full shadow-md hover:bg-gray-100 transition">
                Get Started
            </button>
        </div>

    </section>

</body>

</html>