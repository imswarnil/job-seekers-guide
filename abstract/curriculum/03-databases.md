# Module 3 — Databases in Practice

**Course code:** `DB-201` · **Duration:** 3 weeks

`CS-DB-101` in Semester 2 teaches the theory. This module is the hands-on
counterpart: a real database on your machine, real data, real queries, real
slowness to fix.

## Lessons

### Getting real
1. Install MySQL/PostgreSQL locally — and a GUI client
2. Your first schema — modelling a real thing (a library, a shop, a school)
3. Loading real data, and what dirty data looks like

### SQL that gets you hired
4. SELECT, WHERE, ORDER BY, LIMIT — fluency drills
5. **JOINs** — inner, left, right, self, with a diagram per join, until they're
   automatic
6. GROUP BY, HAVING, aggregates
7. Subqueries and CTEs
8. INSERT, UPDATE, DELETE — and the WHERE clause you forgot, once
9. **The 20 SQL questions asked in interviews**, worked through

### Making it not slow
10. Indexes — what they are, when they help, when they hurt
11. EXPLAIN — reading a query plan
12. The five reasons your query is slow

### Correctness
13. Transactions, commits, rollbacks
14. ACID, in practice not in definition
15. Concurrency — two users, one row, one problem

### Design
16. Schema design for a real feature, start to finish
17. Normalisation applied — and when to deliberately denormalise
18. Migrations — changing a schema that already has data in it

### Beyond relational
19. NoSQL — what MongoDB/Redis are for, and when relational is still right
20. Redis as a cache — the one NoSQL tool nearly every backend job touches

## Connecting from your language

A short track per language, matching Module 2 stage 8:

| Language | Covered |
|---|---|
| Java | JDBC, connection pooling, a taste of Hibernate |
| Python | sqlite3, psycopg2, SQLAlchemy basics |
| TypeScript | pg/mysql2, an ORM (Prisma), migrations |

## Outcome

The learner can design a schema for a feature, write any join without looking it
up, explain why a query is slow, and connect a real application to a real
database.

## Interview link

SQL is one of the most reliably tested skills in fresher interviews *and* one of
the most reliably weak. A candidate genuinely fluent in joins and group-by is
already unusual.
