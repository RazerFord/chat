function message(text) {
    jQuery('#chat-result').append(text)
}

jQuery(document).ready(function ($) {

    let socket = new WebSocket("ws://127.0.0.1:8090")

    socket.onopen = function () {
        message("<div style=\"color: #00ff00\">Cоединение установлено</div>")
    }

    socket.onerror = function (error) {
        message("<div>Ошибка при соедининении" + (error.message ? error.message : "") + "</div>")
    }

    socket.onclose = function () {
        message("<div>Соединение закрыто</div>")
    }

    socket.onmessage = function (event) {
        let data = JSON.parse(event.data)
        message("<div class=\"chat-result\">" + data.type + " - " + data.message + "</div>")
    }

    $("#chat").on('submit', function () {

        let message = {
            chat_message: $("#chat-message").val(),
            chat_user: $("#chat-user").val(),
        }

        if (message.chat_user === "") {
            jQuery('#chat-result').append('<div>Введите имя</div>')
        } else {
            $("#chat-user").attr("type", "hidden");
            socket.send(JSON.stringify(message));
        }

        return false
    });
})