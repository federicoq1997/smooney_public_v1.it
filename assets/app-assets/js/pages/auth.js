// Auth V2
if ($(".hp-authentication-page-register").length) {
    $(".hp-authentication-page-register *[data-button-step]").click(function () {
        let buttonItem = $(this);

        if (buttonItem.attr("data-button-step") === "register-step-1") {
            $(this).addClass("d-none")
            $(".hp-authentication-page-register *[data-button-step='register-step-2']").removeClass("d-none")

            $(".hp-authentication-page-register *[data-step]").each(function () {
                if ("register-step-1" === $(this).attr("data-step")) {
                    $(this).removeClass("d-none")
                }
            });
        }
       
        if (buttonItem.attr("data-button-step") === "register-step-2") {
            $(this).addClass("d-none")
            $(".hp-authentication-page-register *[data-button-step='register-step-3']").removeClass("d-none")

            $(".hp-authentication-page-register *[data-step]").each(function () {
                if ("register-step-2" === $(this).attr("data-step")) {
                    $(this).removeClass("d-none")
                }
            });
        }
       
        if (buttonItem.attr("data-button-step") === "register-step-3") {
            $(this).addClass("d-none")
            $(".hp-authentication-page-register *[data-button-step='register-step-4']").removeClass("d-none")

            $(".hp-authentication-page-register *[data-step]").each(function () {
                if ("register-step-3" === $(this).attr("data-step")) {
                    $(this).removeClass("d-none")
                }
            });
        }
    });
} else {
    $(".hp-authentication-page *[data-button-step]").click(function () {
        let buttonItem = $(this);

        $(".hp-authentication-page *[data-step]").each(function () {
            if (buttonItem.attr("data-button-step") === $(this).attr("data-step")) {
                $(this).removeClass("d-none")
                buttonItem.addClass("d-none")
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    let form = document.getElementById('otp-form');
    if(!form) form = document.getElementById('recover-password');
    const inputs = [...form.querySelectorAll('input[type=text]')]

    const handleKeyDown = (e) => {
        if (
            !/^[0-9]{1}$/.test(e.key)
            && e.key !== 'Backspace'
            && e.key !== 'Delete'
            && e.key !== 'Tab'
            && !e.metaKey
        ) {
            e.preventDefault()
        }

        if (e.key === 'Delete' || e.key === 'Backspace') {
            const index = inputs.indexOf(e.target);
            if (index > 0) {
                inputs[index - 1].value = '';
                inputs[index - 1].focus();
            }
        }
    }

    const handleInput = (e) => {
        const { target } = e
        const index = inputs.indexOf(target)
        if (target.value) {
            if (index < inputs.length - 1) {
                inputs[index + 1].focus()
            }
        }
    }

    const handleFocus = (e) => {
        e.target.select()
    }

    const handlePaste = (e) => {
        e.preventDefault()
        const text = e.clipboardData.getData('text')
        if (!new RegExp(`^[0-9]{${inputs.length}}$`).test(text)) {
            return
        }
        const digits = text.split('')
        inputs.forEach((input, index) => input.value = digits[index])
    }

    inputs.forEach((input) => {
        input.addEventListener('input', handleInput)
        input.addEventListener('keydown', handleKeyDown)
        input.addEventListener('focus', handleFocus)
        input.addEventListener('paste', handlePaste)
    })
}) 
