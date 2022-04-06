<script>
    import { afterUpdate, onMount } from 'svelte'
    import axios from 'axios'
    import { _ } from 'svelte-i18n'

    import Select from 'svelte-select'
    import InputError from '@/Shared/InputError'

    export let classes = ''
    export let id = ''
    export let message
    export let formLineaProgramatica
    export let required

    let lineasProgramaticas = []
    let lineaProgramaticaFiltered = null
    let select = null

    onMount(() => {
        getLineaProgramatica()
        select = document.getElementById(id)
    })

    afterUpdate(() => {
        if (required) {
            formLineaProgramatica != null && select != null ? select.setCustomValidity('') : select.setCustomValidity($_('Please fill out this field.'))
        }
    })

    async function getLineaProgramatica() {
        let res = await axios.get(route('web-api.lineas-programaticas', 0))
        lineasProgramaticas = res.data
        selectLineaProgramatica()
    }

    function handleLineaProgramatica(event) {
        formLineaProgramatica = event.detail.value
    }

    function selectLineaProgramatica() {
        if (formLineaProgramatica) {
            let filterItem = lineasProgramaticas.filter(function (lineaProgramatica) {
                return lineaProgramatica.value == formLineaProgramatica
            })
            lineaProgramaticaFiltered = filterItem[0]
        }
    }
</script>

<Select bind:selectedValue={lineaProgramaticaFiltered} inputAttributes={{ id: id }} listPlacement="bottom" placeholder="Busque por el nombre de la línea programática" containerClasses="lineas-programaticas {classes}" items={lineasProgramaticas} on:select={handleLineaProgramatica} on:clear={() => (formLineaProgramatica = null)} noOptionsMessage="No hay ítems, por favor revise los filtros" />
<InputError {message} />

<style>
    :global(.lineas-programaticas .listItem) {
        border-bottom: 1px solid #ccc;
    }

    :global(.lineas-programaticas .item) {
        height: auto !important;
        line-height: 1.6 !important;
        text-overflow: initial !important;
        overflow: initial !important;
        white-space: break-spaces !important;
        padding-top: 10px;
        padding-bottom: 10px;
    }
</style>
