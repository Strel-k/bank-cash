<!-- resources/views/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>B-Cash Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Set global variables for authentication
        window.csrfToken = '{{ csrf_token() }}';
        window.userId = '{{ Auth::id() }}';
        window.appUrl = '{{ url('/') }}';
        window.apiToken = '{{ session("api_token") }}';
    </script>
</head>
<body>
    <header>
        <h1>Welcome, {{ Auth::user()->full_name }}</h1>
        <a href="{{ route('logout') }}">Logout</a>
    </header>

    <main>
        <section>
            <h2>Wallet Balance: <span id="balanceDisplay">₱0.00</span></h2>
            <button onclick="refreshBalance()">Refresh</button>
        </section>

        <section>
            <h2>Add Money</h2>
            <input type="number" id="addAmount" placeholder="Enter amount">
            <button onclick="addMoney()">Add Money</button>
            <p id="addMoneyMsg"></p>
        </section>

        <section>
            <h2>Send Money</h2>
            <input type="text" id="receiverPhone" placeholder="Receiver phone number">
            <input type="number" id="sendAmount" placeholder="Amount">
            <input type="text" id="sendDesc" placeholder="Description">
            <button onclick="sendMoney()">Send Money</button>
            <p id="sendMoneyMsg"></p>
        </section>

        <section>
            <h2>Pay Bills</h2>
            <input type="text" id="billAccount" placeholder="Bill account">
            <input type="number" id="billAmount" placeholder="Amount">
            <button onclick="payBills()">Pay Bill</button>
            <p id="payBillsMsg"></p>
        </section>

        <section>
            <h2>Search Users</h2>
            <input type="text" id="searchQuery" placeholder="Search by phone or name" oninput="searchUsers()">
            <div id="userList"></div>
        </section>

        <section>
            <h2>Transaction History</h2>
            <div id="transactionHistory">Loading transactions...</div>
        </section>
    </main>

<script src="{{ asset('js/dashboard.js') }}?v={{ time() }}"></script>
</body>
</html>