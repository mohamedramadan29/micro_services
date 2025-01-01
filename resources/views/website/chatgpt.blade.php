<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatGPT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #4facfe, #00f2fe);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .chat-container {
            width: 400px;
            height: 600px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-header {
            background: #0078d7;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }
        .chat-body {
            flex: 1;
            padding: 10px;
            overflow-y: auto;
            background: #f4f4f4;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
            animation: fadeIn 0.5s;
        }
        .message.user {
            background: #0078d7;
            color: white;
            margin-left: auto;
        }
        .message.bot {
            background: #e1e1e1;
            color: black;
            margin-right: auto;
        }
        .chat-footer {
            display: flex;
            padding: 10px;
            background: #fff;
            border-top: 1px solid #ddd;
        }
        .chat-footer input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
        }
        .chat-footer button {
            margin-left: 10px;
            padding: 10px 15px;
            background: #0078d7;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .chat-footer button:hover {
            background: #005bb5;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">Chat with ChatGPT</div>
        <div class="chat-body" id="chatBody">
            <!-- Messages will appear here -->
        </div>
        <div class="chat-footer">
            <input type="text" id="messageInput" placeholder="Type your message...">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        const chatBody = document.getElementById('chatBody');
        const messageInput = document.getElementById('messageInput');

        function appendMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            messageDiv.textContent = text;
            chatBody.appendChild(messageDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        async function sendMessage() {
            const message = messageInput.value;
            if (!message) return;

            appendMessage(message, 'user');
            messageInput.value = '';

            try {
                const response = await fetch('/chatgpt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ message }),
                });

                const data = await response.json();
                if (data.message) {
                    appendMessage(data.message, 'bot');
                } else {
                    appendMessage('Sorry, something went wrong.', 'bot');
                }
            } catch (error) {
                appendMessage('Error connecting to the server.', 'bot');
            }
        }
    </script>
</body>
</html>
