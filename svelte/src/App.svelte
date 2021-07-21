<script lang="ts">
import { onMount} from "svelte";
import { mainView } from "./stores/mainView";

onMount(() => {
    mainView.switchView('loggedOut');
});

</script>

<svelte:component this={$mainView} />
