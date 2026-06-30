console.log("✅ chatbot.js cargado");

document.addEventListener("DOMContentLoaded", () => {

    /*==================================================
        ELEMENTOS
    ==================================================*/

    const root = document.getElementById("procafesChat");

    if (!root) return;

    const url = root.dataset.sendUrl;

    const form = document.getElementById("chatForm");
    const input = document.getElementById("chatMessage");
    const messages = document.getElementById("chatMessages");
    const typing = document.getElementById("typingIndicator");
    const button = document.getElementById("chatSendButton");

    const csrf = document
        .querySelector('meta[name="csrf-token"]')
        .content;


    /*==================================================
        VENTANA
    ==================================================*/

    const chatbotWindow =
        document.getElementById("chatbotWindow");

    const chatbotToggle =
        document.getElementById("chatbotToggle");

    const chatbotClose =
        document.getElementById("chatbotClose");

    /* Abrir / cerrar con el botón flotante */
    chatbotToggle?.addEventListener("click", () => {

        if (chatbotWindow.classList.contains("show")) {

            chatbotWindow.classList.remove("show");

        } else {

            chatbotWindow.classList.add("show");

            input.focus();

        }

    });

    /* Cerrar con la X */
    chatbotClose?.addEventListener("click", () => {

        chatbotWindow.classList.remove("show");

    });

    /*==================================================
        SCROLL
    ==================================================*/

    function scrollBottom() {

        messages.scrollTop =
            messages.scrollHeight;

    }

    /*==================================================
        MENSAJE USUARIO
    ==================================================*/

    function userMessage(text){

        messages.insertAdjacentHTML(

            "beforeend",

            `
            <div class="user-message">

                <div class="bubble">

                    ${text}

                </div>

            </div>
            `

        );

        scrollBottom();

    }

    /*==================================================
        MENSAJE BOT
    ==================================================*/

    function botMessage(text){

        messages.insertAdjacentHTML(

            "beforeend",

            `
            <div class="bot-message">

                <div class="bubble">

                    ${text}

                    <br><br>

                    <small class="text-muted">

                        💬 ¿Hay algo más en lo que pueda ayudarte?

                    </small>

                </div>

            </div>
            `

        );

        scrollBottom();

    }

    /*==================================================
        ENVIAR MENSAJE
    ==================================================*/

    async function sendMessage(text){

        userMessage(text);

        typing.classList.remove("d-none");

        button.disabled = true;

        try{

            const response = await fetch(url,{

                method:"POST",

                headers:{

                    "Content-Type":"application/json",

                    "Accept":"application/json",

                    "X-CSRF-TOKEN":csrf

                },

                body:JSON.stringify({

                    mensaje:text

                })

            });

            const data = await response.json();

            typing.classList.add("d-none");

            botMessage(

                data.message ??

                "No encontré información."

            );

            if(data.products){

                data.products.forEach(product=>{

                    messages.insertAdjacentHTML(

                        "beforeend",

                        productCard(product)

                    );

                });

                document
                    .querySelectorAll(".add-cart")
                    .forEach(btn=>{

                        btn.onclick=()=>{

                            addToCart(

                                btn.dataset.product,

                                btn

                            );

                        };

                    });

            }

            scrollBottom();

        }

        catch(error){

            typing.classList.add("d-none");

            botMessage(

                "❌ Ocurrió un error al procesar tu consulta."

            );

            console.error(error);

        }

        button.disabled = false;

    }

        /*==================================================
        TARJETA PRODUCTO
    ==================================================*/

    function productCard(product){

        return `

        <div class="card chat-card">

            ${
                product.image
                ? `
                    <img
                        src="${product.image}"
                        class="card-img-top"
                        alt="${product.name}"
                    >
                `
                : ""
            }

            <div class="card-body">

                <span class="badge bg-success mb-2">

                    ${product.category}

                </span>

                <h5 class="card-title">

                    ${product.name}

                </h5>

                <p class="card-text small text-muted">

                    ${product.description}

                </p>

                <div class="d-flex justify-content-between align-items-center">

                    <strong class="text-success">

                        ${product.price}

                    </strong>

                    <span class="${
                        product.available
                        ? "text-success"
                        : "text-danger"
                    }">

                        ${
                            product.available
                            ? "Disponible"
                            : "Agotado"
                        }

                    </span>

                </div>

                <button

                    class="btn btn-success w-100 mt-3 add-cart"

                    data-product="${product.id}"

                >

                    🛒 Agregar al carrito

                </button>

            </div>

        </div>

        `;

    }

    /*==================================================
        AGREGAR AL CARRITO
    ==================================================*/

    async function addToCart(productId,button){

        button.disabled = true;

        button.innerHTML = "Agregando...";

        try{

            const response = await fetch("/cart/add",{

                method:"POST",

                headers:{

                    "Content-Type":"application/json",

                    "Accept":"application/json",

                    "X-CSRF-TOKEN":csrf

                },

                body:JSON.stringify({

                    product_id:productId,

                    qty:1

                })

            });

            const data = await response.json();

            if(!response.ok){

                throw new Error(

                    data.message ??

                    "No se pudo agregar."

                );

            }

            botMessage(

                "✅ ¡Producto agregado correctamente al carrito!"

            );

            if(typeof window.refreshCart==="function"){

                window.refreshCart();

            }

        }

        catch(error){

            botMessage(

                "❌ " + error.message

            );

        }

        button.disabled = false;

        button.innerHTML = "🛒 Agregar al carrito";

    }

    /*==================================================
        FORMULARIO
    ==================================================*/

    form.addEventListener("submit",(e)=>{

        e.preventDefault();

        const text = input.value.trim();

        if(text==="") return;

        input.value="";

        sendMessage(text);

    });

    /*==================================================
        BOTONES RÁPIDOS
    ==================================================*/

    document.querySelectorAll(".quick-btn").forEach(btn=>{

        btn.addEventListener("click",()=>{

            sendMessage(

                btn.dataset.question

            );

        });

    });

    /*==================================================
        ENTER
    ==================================================*/

    input.addEventListener("keydown",(e)=>{

        if(e.key==="Enter"){

            e.preventDefault();

            form.requestSubmit();

        }

    });

    /*==================================================
        CERRAR CON ESC
    ==================================================*/

    document.addEventListener("keydown",(e)=>{

        if(e.key==="Escape"){

            chatbotWindow.classList.remove("show");

        }

    });

});