import type { ComputedRef, InjectionKey } from 'vue'

/**
 * Injection contracts between the diagram containers and their steps.
 *
 * Typed keys rather than strings, because these are provided by one MDC
 * component and injected by another that an author writes on a separate line of
 * markdown — a mistyped string would fail silently in the middle of a lesson.
 */

export interface FlowContext {
  numbered: ComputedRef<boolean>
  direction: ComputedRef<'horizontal' | 'vertical'>
  /** Claim the next index in document order. Called once, during setup. */
  claim: () => number
}

export const flowKey = Symbol('flow') as InjectionKey<FlowContext>

export interface TimelineContext {
  claim: () => number
}

export const timelineKey = Symbol('timeline') as InjectionKey<TimelineContext>

export interface CodeTraceContext {
  register: (step: { lines: string, caption?: string }) => number
  active: ComputedRef<number>
}

export const codeTraceKey = Symbol('code-trace') as InjectionKey<CodeTraceContext>

export interface CompareContext {
  claim: () => number
}

export const compareKey = Symbol('compare') as InjectionKey<CompareContext>
