{{--==========================================================
    BOTÓN FLOTANTE CHATBOT
===========================================================--}}

<button
    type="button"
    class="chatbot-button"
    data-bs-toggle="modal"
    data-bs-target="#chatbotModal"
    aria-label="Abrir asistente PROCAFES"
>

    <img
        src="{{ asset('images/chatbot-icon.png') }}"
        class="chatbot-icon"
        alt="Asistente PROCAFES"
    >

</button>

{{--==========================================================
    MODAL CHATBOT
===========================================================--}}

<div
    class="modal fade"
    id="chatbotModal"
    tabindex="-1"
    aria-labelledby="chatbotModalLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-scrollable chatbot-dialog">

        <div class="modal-content chatbot-modal">

            <!-- Cabecera -->

            <div class="modal-header chatbot-header">

                <div class="d-flex align-items-center">

                    <img
                        src="{{ asset('images/chatbot-icon.png') }}"
                        alt="PROCAFES"
                        class="chatbot-header-icon"
                    >

                    <div class="ms-3">

                        <h5
                            class="mb-0"
                            id="chatbotModalLabel"
                        >
                            Asistente PROCAFES
                        </h5>

                        <small class="chat-status">

                            🟢 En línea

                        </small>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                ></button>

            </div>

            <!-- Cuerpo -->

            <div class="modal-body p-0 chatbot-body">

                <x-chat.window />

            </div>

        </div>

    </div>

</div>