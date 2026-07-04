import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Badge } from "./Badge.vue"

export const badgeVariants = cva("badge", {
  variants: {
    variant: {
      default: "badge--default",
      secondary: "badge--secondary",
      destructive: "badge--destructive",
      outline: "badge--outline",
    },
  },
  defaultVariants: {
    variant: "default",
  },
})
export type BadgeVariants = VariantProps<typeof badgeVariants>
