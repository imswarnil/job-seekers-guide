---
title: SQL — learn to store and ask
description: Your Java program forgot everything the moment it stopped, and a flat file cannot answer a question. SQL has barely changed in decades, which is exactly why it is worth learning properly.
code: JSG-02
duration: 10 weeks
stage: language
icon: i-lucide-database
outcomes:
  - Design a relational schema that survives contact with real, messy data
  - Write joins, subqueries and window functions you can defend line by line
  - Tell the difference between a query that runs and a query that is right
  - Change data safely, on purpose, with a way back
  - Build the reporting layer every UniversityOS dashboard will later read
prerequisites:
  - java
---

Your data lives in a flat file that gets slower and messier by the day. Ask it
"which department has the most backlogs?" and you would have to write a program.
There is a language built for exactly that question.

## Why this is the most valuable track here

Every data-touching role expects SQL. Analysts, back-end developers, data
engineers, and a surprising number of product and operations people. The syntax
is small enough to learn in a fortnight and deep enough that people are still
getting better at it twenty years in.

## Two disciplines, taught from the first lesson

::feature-list{columns="2"}
  :::feature{icon="i-lucide-scan-eye" title="Correct, not just runnable"}
  A query that executes without an error can be silently, expensively wrong. Does
  the row count make sense? Are there unexpected empties? A green tick is not
  correctness, and this track teaches reading a result critically.
  :::
  :::feature{icon="i-lucide-shield-alert" title="Safe"}
  There is no undo on a database. Know where you are, select before you write,
  never write without a `WHERE`, wrap risky writes in a transaction, and practise
  destructive commands on a copy.
  :::
::

::warning{icon="i-lucide-alert-triangle"}
Joins are the concept that separates "can write a `SELECT`" from "can query", and
window functions are a new way of thinking rather than new syntax. Both chapters
open by saying so and go slowly. Do not skim them.
::

## The chapters

::flow{numbered direction="vertical" caption="Thirteen chapters, ending in the reporting layer the dashboards will read."}
  :::flow-step{label="Before you write a query" icon="i-lucide-table"}
  Why not one giant spreadsheet, what a relational database is, keys and
  relationships, and how to stay safe on a live database.
  :::
  :::flow-step{label="Asking for exactly what you want" icon="i-lucide-search"}
  `SELECT`, `DISTINCT`, aliases, `ORDER BY`, `LIMIT` — and reading a result grid.
  :::
  :::flow-step{label="Filtering and shaping" icon="i-lucide-filter"}
  `WHERE`, precedence, `IN`, `LIKE`, the three-valued logic of `NULL`, then string,
  date and numeric functions and `CASE`.
  :::
  :::flow-step{label="Summarising" icon="i-lucide-sigma"}
  Aggregates, `GROUP BY`, `HAVING`, and the two counts that disagree on the same
  table.
  :::
  :::flow-step{label="Joins" icon="i-lucide-git-merge" highlight}
  Inner, left, anti-joins, self-joins — and fan-out, where your counts quietly
  start to lie.
  :::
  :::flow-step{label="Subqueries and window functions" icon="i-lucide-layers"}
  Queries inside queries, `EXISTS`, derived tables, then partitions, ranking,
  running totals and `LAG`.
  :::
  :::flow-step{label="Design, safe writes and speed" icon="i-lucide-gauge"}
  Data types, constraints, normalisation, `INSERT`/`UPDATE`/`DELETE` with a way
  back, transactions, indexes and reading `EXPLAIN`.
  :::
  :::flow-step{label="The reporting layer" icon="i-lucide-chart-line"}
  Funnel analysis, cohorts and retention, and the at-risk early-warning report.
  :::
::

## What UniversityOS becomes

The real relational schema behind everything — `students`, `programs`,
`applications`, `fee_payments`, `exam_results`, `student_attendance`,
`placements` and the rest — plus the view layer that every dashboard, and later
every AI feature, reads from.

::callout{icon="i-lucide-arrow-right"}
You can now store the university's data and ask it anything. And still, the only
person on earth who can use any of it is you, sitting at your own machine.
::
