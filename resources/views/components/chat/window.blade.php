<div
    id="procafesChat"
    data-send-url="{{ url('/chatbot') }}"
>

    <!-- ===========================
        MENSAJES
    ============================ -->

    <div
        id="chatMessages"
        class="chat-messages"
    >

        <div class="bot-message">

            <div class="bubble">

                👋 <strong>¡Hola!</strong>

                <br><br>

                Soy el asistente virtual de
                <strong>PROCAFES</strong>.

                Estoy aquí para ayudarte con información sobre nuestros productos, horarios y servicios.

                <br><br>

                Puedes preguntarme por:

                <div class="quick-actions">

                    <button
                        class="quick-btn"
                        data-question="¿Qué cafés tienen?"
                    >
                        ☕ Cafés
                    </button>

                    <button
                        class="quick-btn"
                        data-question="¿Qué bebidas frías tienen?"
                    >
                        🥤 Bebidas
                    </button>

                    <button
                        class="quick-btn"
                        data-question="Muéstrame los snacks"
                    >
                        🍔 Snacks
                    </button>

                    <button
                        class="quick-btn"
                        data-question="¿Qué postres tienen?"
                    >
                        🍰 Postres
                    </button>

                    <button
                        class="quick-btn"
                        data-question="¿Cuál es su horario?"
                    >
                        🕒 Horario
                    </button>

                    <button
                        class="quick-btn"
                        data-question="¿Dónde están ubicados?"
                    >
                        📍 Ubicación
                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- ===========================
        ESCRIBIENDO
    ============================ -->

    <div
        id="typingIndicator"
        class="typing d-none"
    >

        🤖 Escribiendo...

    </div>

    <!-- ===========================
        INPUT
    ============================ -->

    <div class="chat-input">

        <form id="chatForm">

            @csrf

            <div class="input-group">

                <input

                    id="chatMessage"

                    type="text"

                    class="form-control"

                    placeholder="Escribe tu pregunta..."

                    autocomplete="off"

                >

                <button

                    id="chatSendButton"

                    class="btn btn-dark"

                    type="submit"

                >

                    <i class="bi bi-send-fill"></i>

                </button>

            </div>

        </form>

        <small
            class="text-muted d-block mt-2 text-center"
        >

            💬 Pregunta por cafés, bebidas, snacks, postres, horarios o ubicación.

        </small>

    </div>

</div>