<script lang="ts" setup>
import type { HTMLAttributes } from "vue"
import { MoreHorizontal } from "@lucide/vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <span
    data-slot="breadcrumb-ellipsis"
    role="presentation"
    aria-hidden="true"
    :class="cn('breadcrumb-ellipsis', props.class)"
  >
    <slot>
      <MoreHorizontal />
    </slot>
    <span class="sr-only">More</span>
  </span>
</template>
