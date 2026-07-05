<script lang="ts" setup>
import type { HTMLAttributes } from "vue"
import { ChevronRight } from "@lucide/vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <li
    data-slot="breadcrumb-separator"
    role="presentation"
    aria-hidden="true"
    :class="cn('breadcrumb-separator', props.class)"
  >
    <slot>
      <ChevronRight />
    </slot>
  </li>
</template>
