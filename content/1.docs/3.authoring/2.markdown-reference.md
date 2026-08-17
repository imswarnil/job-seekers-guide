---
title: Markdown reference
description: Everything you can use inside a lesson — prose, code, callouts, steps, tabs and cards — with the source next to the result.
---

Lessons are [MDC](https://content.nuxt.com/docs/files/markdown) — ordinary markdown
plus a component syntax. Everything below works in any lesson, doc or blog post.

## Prose

Standard markdown covers most of a lesson: headings, **bold**, _italic_, `inline
code`, lists, tables, [links](/docs/curriculum) and blockquotes.

> Keep paragraphs short. This is read on a phone, often late at night, often by
> somebody who is tired.

## Code

Add a filename after the language and it appears as a tab:

```java [Hello.java]
public class Hello {
    public static void main(String[] args) {
        System.out.println("This is the whole program.");
    }
}
```

Highlight the lines that matter with `{2,4-6}` after the language.

## Callouts

::callout{icon="i-lucide-lightbulb"}
A neutral aside. Use for context that is genuinely optional.
::

::tip{icon="i-lucide-check"}
Use for the thing that makes it click.
::

::warning{icon="i-lucide-alert-triangle"}
Use for the mistake everybody makes here.
::

::note
Use for the interview question this lesson answers.
::

## Steps

::steps{level="3"}

### Install the JDK

Any build of Java 21 or later. On Ubuntu, `sudo apt install openjdk-21-jdk`.

### Check it

`java -version` should print a version, not "command not found".

### Write the file

Create `Hello.java` with the code above and run `java Hello.java`.

::

## Cards

::card-group
  ::card{title="A related lesson" icon="i-lucide-book-open" to="/courses"}
  Cards are the right shape for "read this next".
  ::

  ::card{title="A resource" icon="i-lucide-external-link" to="https://content.nuxt.com"}
  They work for outbound links too.
  ::
::

## Tabs

::tabs
  :::tabs-item{label="Java" icon="i-lucide-coffee"}
  The default recommendation, and the one with the most entry-level roles.
  :::

  :::tabs-item{label="Python" icon="i-lucide-code"}
  Fine. Fewer entry-level backend roles in this market than Java.
  :::
::

## Accordions

::accordion
  :::accordion-item{label="Do I need maths for this?" icon="i-lucide-circle-help"}
  No. See [what we exclude](/docs/curriculum/what-we-exclude).
  :::

  :::accordion-item{label="Can I skip this lesson?" icon="i-lucide-circle-help"}
  Say so explicitly at the top of the lesson if the answer is yes, and say what
  the reader gives up by skipping.
  :::
::

## Field lists

For anything that has a name, a type and a meaning:

::field{name="minutes" type="number"}
Reading estimate for the lesson. Drives the module time total.
::

::field{name="kind" type="'lesson' | 'practice' | 'project' | 'quiz'"}
Changes the icon shown next to the lesson in the player sidebar.
::
