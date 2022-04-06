<script context="module">
    import { writable } from 'svelte/store'
    export const title = writable(null)
</script>

<script>
    import { Inertia } from '@inertiajs/inertia'
    import { route } from '@/Utils'
    import Button from '@/Shared/Button'

    export let status

    $title = 'SGPS-SIPRO - Error ' + status
</script>

<svelte:head>
    <title>SGPS-SIPRO - Error {status}</title>
</svelte:head>

<div class="bg-indigo-700 flex flex-col items-center justify-center min-h-screen text-white">
    <figure>
        <img src={window.basePath + '/images/error.png'} alt="Error" class="w-2/3 m-auto mb-10" />
    </figure>

    <div>
        {#if status == 403}
            <h1 class="text-2xl text-center">Está acción no está autorizada para su rol.</h1>
        {:else if status == 404}
            <h1 class="text-2xl text-center">La página que busca no existe.</h1>

            <div class="mt-20">
                <p>Posibles motivos por los que la página solicitada no se encuentra disponible:</p>
                <ul class="list-disc mt-5">
                    <li>Puede que haya cambiado de dirección (URL).</li>
                    <li>Es posible que está página no exista o no se haya escrito correctamente la URL, compruebe de nuevo y verifique que este bien escrita.</li>
                </ul>
            </div>
        {:else if status == 500}
            <h1 class="text-2xl text-center">Algo está mal en nuestros servidores. Por favor notifique este error a la mesa de ayuda.</h1>
        {:else if status == 503}
            <h1 class="text-2xl text-center">La aplicación está en mantenimiento. Por favor intenta de nuevo en unos minutos.</h1>
        {/if}

        {#if status != 503}
            <div class="mt-10">
                <p>Puede notificar a la mesa de ayuda realizando los siguientes pasos:</p>
                <ul class="list-disc mt-5">
                    <li>Tome un pantallazo del error.</li>
                    <li>Copie la URL.</li>
                    <li>De clic en <strong>Solicitar soporte</strong> y en el campo <strong>Descripción</strong> pegue esa información. Sea lo más descriptivo, de un paso a paso de como ocurrió el error.</li>
                </ul>

                <div class="flex items-center mt-10">
                    <Button on:click={() => Inertia.visit(route('reportar-problemas.create'))} variant="raised" type="button" class="mr-4">Solicitar soporte</Button> o <Button on:click={() => Inertia.visit(route('login'))} variant="raised" class="ml-4">Regresar a la aplicación</Button>
                </div>
            </div>
        {/if}
    </div>
</div>
