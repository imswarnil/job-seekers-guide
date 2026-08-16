<?php
/**
 * Starter content, shipped with the plugin.
 *
 * Written prose rather than placeholder text, because a course platform whose
 * own courses are lorem ipsum is not demonstrating anything. Seeded by
 * Guide\Content\Starter_Content on upgrade, and never overwritten once an
 * operator has edited it.
 *
 * Generated from the drafts in abstract/ — edit there and regenerate, or edit
 * in the console and this file stops applying to that post.
 */

defined( 'ABSPATH' ) || exit;

return array (
  'slug' => 'how-software-actually-works',
  'title' => 'How Software Actually Works',
  'code' => 'CS-000',
  'level' => 'beginner',
  'header' => 'spotlight',
  'tier' => 'free',
  'menu_order' => 0,
  'excerpt' => 'The orientation nobody gives you. What software is, how the industry is shaped, who does what, what the jobs pay, and how people actually get hired — before you write a single line of code.',
  'content' => '<p>This is the module that has to come first, and it is the one almost everybody skips.</p><p>You cannot choose a destination you cannot see. There are hundreds of tools, dozens of roles, and every roadmap on the internet contradicts the last one — so a beginner facing that either picks at random or freezes. Both waste a year.</p><p>So: orientation first, choice second, code third. By the end of this you will know what software actually is, what happens between the text you type and the electricity that runs it, how service companies differ from product companies and why they pay differently, what every team in a software company does all day, what the vocabulary means, what each role really pays a fresher in India, and roughly which direction suits you.</p><p>Everything is taught through one running example — a university exam portal — because it is the one complex system this audience has already lived inside.</p><p>No mathematics. No prior coding. Nothing to buy.</p>',
  'outcomes' => 
  array (
    0 => 'Explain what software is, and what happens between your code and the hardware',
    1 => 'Tell service, product, startup and capability-centre companies apart — and why they pay differently',
    2 => 'Describe what every team in a software company does all day',
    3 => 'Use the vocabulary of the industry without bluffing: sprint, PR, staging, rollback, SLA',
    4 => 'Name what each role really pays a fresher in India, and how hard each door is',
    5 => 'Answer “walk me through how a feature gets built” like somebody who has done it',
    6 => 'Choose a direction to start in, knowing it is cheap to change',
  ),
  'requirements' => 
  array (
    0 => 'No programming experience',
    1 => 'No mathematics',
    2 => 'A phone or laptop, and about six hours',
  ),
  'sections' => 
  array (
    0 => 
    array (
      'title' => 'Part 1 — What software actually is',
      'description' => 'Before any code: what the thing is, how it runs, and what industry you are trying to enter.',
      'lessons' => 
      array (
        0 => 
        array (
          'title' => 'What software actually is',
          'duration' => 9,
          'excerpt' => 'Forget the definitions. You have already used a large software system for three years — you just called it "the college website".',
          'content' => '<p>Every course starts by telling you software is "a set of instructions that tells a computer what to do". True, useless, and instantly forgettable. So let us start somewhere you have actually been.</p>

<h2>You already know a large software system</h2>

<p>Think about your college\'s exam portal. The one that crashed on results day. That thing did all of this:</p>

<ul>
	<li>Stored 4,000 students, each with a roll number, branch, semester and photo</li>
	<li>Stored every subject, who teaches it, and how many credits it carries</li>
	<li>Recorded marks per student per subject per semester</li>
	<li>Calculated SGPA and CGPA using rules somebody wrote down once</li>
	<li>Decided who had a backlog and who was promoted</li>
	<li>Generated a PDF marksheet with a header nobody had updated since 2014</li>
	<li>Let 4,000 people ask for their result <em>in the same four minutes</em></li>
</ul>

<p>That is software. Not a definition — an actual system, with actual users, that actually fell over. And you have opinions about it already, which means you have been doing requirements analysis for three years without being paid for it.</p>

<h2>The three parts, in your own building</h2>

<p>Every piece of software, from your exam portal to WhatsApp, is three things wearing a trenchcoat:</p>

<ol>
	<li><strong>Something that stores facts.</strong> Your roll number, your marks, your attendance. In the exam portal, this is the database. In the physical college, this was a steel almirah in the exam cell.</li>
	<li><strong>Something that applies rules to those facts.</strong> "SGPA is credits times grade points, divided by total credits." "Below 40 is a backlog." "Below 75% attendance and you are not sitting the exam." This is the part people mean when they say <em>logic</em>.</li>
	<li><strong>Something that shows it to a human.</strong> The page you loaded at 11pm and refreshed forty times. This is the interface.</li>
</ol>

<p>Store it. Decide about it. Show it. Every job title you will read about in the next few lessons is a person specialising in one of those three.</p>

<h2>Why the portal crashed</h2>

<p>Here is the part that matters, and it is the reason this module exists before any code.</p>

<p>The portal did not crash because the developer was bad at programming. It crashed because 4,000 people asked one machine the same question simultaneously, and nobody had decided in advance what should happen when they did.</p>

<p>That is not a coding problem. That is a <em>design</em> problem — and design problems are what separate someone who can write a function from someone who gets paid ₹12 lakh to write the same function. Nobody in an interview will ask you to write a marksheet generator. They will absolutely ask you what happens when 4,000 people load it at once.</p>

<p>You will learn the answer to that. It has a name — caching, queueing, load balancing — and it is in the System Design module. For now, the only thing to take away is that the interesting problems are never "how do I write this line".</p>

<h2>Software is a description of rules somebody argued about</h2>

<p>One more thing, because it changes how you will read everything else.</p>

<p>Somewhere in your college, a committee sat in a room and argued about whether a student with 39.5 marks should be rounded up. Somebody won that argument. Then somebody wrote it into the portal as a line of code, and from that day forward the rule was applied to 4,000 students, identically, forever, without the committee being in the room.</p>

<p>That is really what software is: an argument that got settled, written down so precisely that a machine can carry it out without anybody supervising. Which is why so much of this job is not typing. It is finding out what the rule actually is, discovering nobody agrees, and getting them to agree.</p>

<h2>Before the next lesson</h2>

<p>Pick one thing your college did badly with software — the portal, attendance, hostel allocation, fee payment, the library. Write down, in one sentence each: what it stored, what rule it applied, and what it showed you.</p>

<p>Keep it. We are going to build exactly that system, in stages, across this entire platform.</p>',
          'slug' => 'what-software-actually-is',
        ),
        1 => 
        array (
          'title' => 'How a program actually runs',
          'duration' => 11,
          'excerpt' => 'You type English-ish text into a file. Somewhere below that, electricity moves. Here is the whole ladder, in one lesson.',
          'content' => '<p>You will write this line in a few weeks:</p>

<pre><code>int total = credits * gradePoints;</code></pre>

<p>A processor has no concept of "credits", "total", or multiplication as you understand it. So something must happen in between. Almost nobody explains what, and the gap quietly haunts people for years — it is why "it works on my machine" feels like witchcraft instead of a diagnosis.</p>

<h2>The ladder, top to bottom</h2>

<p>Think of it as the college\'s own chain of command, which you have watched work.</p>

<p><strong>You write source code.</strong> Text in a file. That is all it is — a <code>.java</code> file is as much "a program" as a written exam paper is "a graduate". It is a description.</p>

<p><strong>A compiler or interpreter translates it.</strong> This is the exam cell clerk who takes the handwritten mark sheets and turns them into the official format. Java compiles to bytecode; C compiles to machine code; Python is interpreted line by line. Different routes, same purpose: turn something a human can read into something a machine can execute.</p>

<p><strong>The operating system schedules it.</strong> Your program does not own the computer. Windows or Linux decides when it runs, how much memory it may have, and whether it is allowed to open that file. This is the university administration: your department cannot just book the auditorium: it asks, and it is scheduled among everyone else\'s requests.</p>

<p><strong>The processor executes instructions.</strong> Fetch an instruction, decode it, do it, move on. Billions of times a second, and each individual step is almost insultingly simple — add these two numbers, put this here, jump there if this is zero.</p>

<p><strong>Electricity does the actual work.</strong> Voltage high, voltage low. One and zero. Everything above this line is an elaborate agreement about what those voltages <em>mean</em>.</p>

<h2>Why this ladder is worth knowing</h2>

<p>Because every confusing bug you will ever have lives at one specific rung, and knowing the rungs turns panic into a question.</p>

<ul>
	<li>"It works on my machine" — a different rung of the ladder differs. Different Java version, different OS, a file that exists on your laptop and not on the server.</li>
	<li>"It compiled but crashed" — the translation succeeded; the execution did not. Your grammar was fine, your meaning was not.</li>
	<li>"It is slow" — you are asking a lower rung to do something enormous, usually far more times than you realise.</li>
</ul>

<p>The exam portal is a good example again. If the marksheet PDF is wrong, that is your code. If the portal is unreachable, that is probably not your code at all — it is the machine, the network or the operating system, and spending four hours re-reading your own logic is four hours wasted. Knowing which rung to look at first is a real, hireable skill.</p>

<h2>Compiled or interpreted, in one paragraph</h2>

<p>A compiled language does the translation once, in advance, and hands the machine a finished artefact. Faster to run, slower to get going, and it tells you about a whole category of mistakes before it ever runs — which is genuinely useful when you are learning.</p>

<p>An interpreted language translates as it goes. Quicker to try things, slower at runtime, and it will happily run for ten minutes before discovering the typo on line 300.</p>

<p>This is one of the reasons the next module recommends Java. Not because it is fashionable — it is not — but because the compiler catches you being wrong, immediately, with a message, at a stage in your life when being caught is exactly what you need.</p>

<h2>Check yourself</h2>

<p>You do not need to memorise this. You need to be able to answer one question when something breaks: <em>which rung is this?</em> Say it out loud when your code fails this month. It will feel silly. It will also make you noticeably faster than people who have been coding longer than you.</p>',
          'slug' => 'how-a-program-actually-runs',
        ),
        2 => 
        array (
          'title' => 'What "the IT industry" actually means',
          'duration' => 10,
          'excerpt' => 'Everyone says "get into IT" as if it were one building with one door. It is closer to a city, and the doors are not equally hard to open.',
          'content' => '<p>"IT industry" is a phrase used mostly by people who are not in it. Inside, nobody says it. They say "I work at a product company", or "I am in services", or "we are a startup". Those are genuinely different jobs, with different hiring, different pay curves and different daily lives — and choosing without knowing the difference is how people end up three years into work they dislike.</p>

<h2>The scale, so the numbers stop being abstract</h2>

<p>India\'s technology sector employs somewhere north of five million people. Every year, roughly a million and a half engineering graduates come out of Indian colleges. Not all of them want software jobs, and not all software jobs go to engineering graduates — but you can see the shape of the problem immediately.</p>

<p>Here is the part nobody says out loud: the shortage is real <em>and</em> the rejection is real, at the same time. Companies genuinely cannot find enough people they consider hireable, while thousands of graduates genuinely cannot get hired. Both are true. The gap between them is not talent. It is that nobody told one group what the other group was looking for.</p>

<p>That gap is the entire reason this platform exists.</p>

<h2>The four kinds of employer</h2>

<p><strong>Service companies.</strong> TCS, Infosys, Wipro, Accenture, Cognizant, Capgemini. They build and run software <em>for other companies</em>. A bank needs a system; the bank does not want to hire 200 engineers; it pays a service company instead.</p>

<p>They hire in enormous numbers, they hire freshers deliberately, and they will train you. The work is often maintenance rather than invention, the starting pay is modest, and progression early on is slower than you would like. They are also, for a very large number of people, the door that opens — including the person who built this platform.</p>

<p><strong>Product companies.</strong> Google, Microsoft, Zoho, Adobe, Freshworks, Zerodha. They build one thing and sell it to many customers. Fewer roles, much harder interviews, much better pay, and the thing you build is the company\'s actual product rather than a client\'s request.</p>

<p><strong>Startups.</strong> Small, fast, chaotic, occasionally brilliant. You will do five jobs, learn quickly, and carry real responsibility far earlier than you should. You may also not get paid in April. Excellent second job. Risky first one, unless you are certain.</p>

<p><strong>Global capability centres.</strong> The bit nobody explains. A foreign company — a bank, a retailer, a carmaker — opens its own engineering office in Bengaluru or Hyderabad. Not a client, not a vendor: the actual company. Product-company work, service-company hiring volumes, often excellent pay. Under-applied to, because freshers have not heard of them.</p>

<h2>Why this decides what your day feels like</h2>

<p>The important part is not the logo. It is who your work is <em>for</em>.</p>

<p>Take the exam portal again. In a service company, the university is your client: they tell you what they want, you build it, they sign it off, and if they want the marksheet header in Comic Sans then it goes in Comic Sans. In a product company, you are building an exam portal to sell to two hundred universities, so no single university gets to decide, and your job includes arguing about which requests are real.</p>

<p>Same code. Completely different job. People who dislike their work often do not dislike programming at all — they are simply in the wrong one of those two.</p>

<h2>The honest ranking</h2>

<p>You will see a lot of internet snobbery about service companies. Ignore it, and note who is writing it: people who already have jobs.</p>

<p>The fresher tag is a door that opens once. A service company that trains you, pays you something, and puts "software engineer" and a real project on your CV has converted you from a graduate into an experienced hire. That conversion is worth far more than the difference in first-year salary, and it is reversible in a way that a two-year gap on your CV is not.</p>

<p>Start where the door opens. Choose where you want to be from inside the industry, not outside it.</p>',
          'slug' => 'what-the-it-industry-actually-means',
        ),
        3 => 
        array (
          'title' => 'Where the money comes from',
          'duration' => 8,
          'excerpt' => 'Your salary is not decided by how well you code. It is decided by how your employer makes money — and that is knowable in advance.',
          'content' => '<p>This is the lesson people skip, and it is the one that explains almost everything confusing about a first job: why one company pays double for the same work, why your manager cares about deadlines more than elegance, why "we cannot do that" happens.</p>

<h2>Two ways to sell software</h2>

<p>Imagine two companies, both building an exam portal for universities.</p>

<p><strong>Company A sells time.</strong> The university pays for eight engineers for a year. If it takes longer, Company A earns more. If Company A needs more engineers, it hires them. Revenue grows by adding people.</p>

<p><strong>Company B sells the product.</strong> It builds one exam portal and licenses it to two hundred universities at ₹4 lakh a year each. The two hundred-and-first customer costs almost nothing to serve. Revenue grows without adding people.</p>

<p>Every consequence follows from those two sentences.</p>

<h2>What that means for you, concretely</h2>

<table>
	<tr><th></th><th>Selling time (services)</th><th>Selling the product</th></tr>
	<tr><td>How they grow</td><td>Hire more people</td><td>Sell more copies</td></tr>
	<tr><td>So hiring is</td><td>Large-volume, fresher-friendly</td><td>Small, selective, expensive</td></tr>
	<tr><td>You are seen as</td><td>Billable capacity</td><td>Leverage on the product</td></tr>
	<tr><td>Your pay comes from</td><td>A rate the client agreed</td><td>Revenue you help multiply</td></tr>
	<tr><td>Which means</td><td>Modest and predictable</td><td>Higher and more variable</td></tr>
</table>

<p>Nobody is being generous or stingy. A company that sells hours cannot pay you more than the hours are sold for. A company whose product reaches a million users can pay one excellent engineer a great deal, because the work multiplies.</p>

<h2>Why your manager behaves the way they do</h2>

<p>In a service company, if the project runs three months late, the client is angry and the contract is at risk. Deadlines are contractual. This is why your beautiful refactor gets rejected: it does not move the delivery date, and the delivery date is what the company sold.</p>

<p>In a product company, shipping the wrong thing on time is worse than shipping the right thing late — because there is no client to be angry, only users who quietly leave. This is why product companies argue endlessly about <em>what</em> to build, which service-company engineers find maddening, and vice versa.</p>

<p>Neither is dysfunction. Both are the money model, showing through.</p>

<h2>How to use this in an interview</h2>

<p>This is not theory — it is one of the cheapest ways to sound like somebody who has thought about the job.</p>

<p>When they ask "why do you want to work here", the weak answer is "your company is a great place to learn". The strong answer names how they make money and connects it to the work: <em>"You sell to universities on subscription, so retention matters more than new sales, which means reliability during results week is probably the highest-value engineering problem you have."</em></p>

<p>You can research that in fifteen minutes. Almost nobody does. That is the whole point of this module: the things that make a candidate look serious are usually cheap, and simply nobody told them.</p>

<h2>The lesson underneath the lesson</h2>

<p>Your salary is not primarily a measure of your skill. It is a measure of how much value your work multiplies, which is decided mostly by the business model you joined.</p>

<p>Which is good news, because it means a modest first salary is not a verdict on you. It is a fact about where you started — and the single most reliable way to raise it is to change where you are standing, not to become 40% better at Java. That is the Offer &amp; Switch module, and it is why it exists.</p>',
          'slug' => 'where-the-money-comes-from',
        ),
        4 => 
        array (
          'title' => 'Checkpoint: can you see the industry yet?',
          'duration' => 6,
          'excerpt' => 'Five questions. Not to grade you — to check the picture in your head is a picture and not a fog.',
          'content' => '<p>Answer these out loud or in writing before moving on. If a question makes you uneasy, the lesson it comes from is worth re-reading — that discomfort is the useful part.</p>

<h3>1. Name the three parts of any software system, using an example from your own college.</h3>
<p><em>Looking for:</em> something that stores facts, something that applies rules, something that shows a human. Attendance, fees, hostel allocation — any of them work.</p>

<h3>2. Your program compiled but crashed when it ran. Which rung of the ladder failed, and which one did not?</h3>
<p><em>Looking for:</em> translation succeeded, execution failed. The grammar was right; the meaning was wrong. You should not be re-reading your syntax.</p>

<h3>3. A friend says "service companies are a waste of time, only join a product company". Give the strongest argument against them, and the strongest argument for them.</h3>
<p><em>Looking for:</em> against — the fresher tag opens once, and experience compounds from wherever it starts. For — pay curves and the kind of work genuinely do differ, and staying too long has a real cost. Both are true. If you can only argue one side, you are not ready to choose.</p>

<h3>4. Two companies pay ₹3.5 LPA and ₹11 LPA for what looks like the same job. Give the business reason, not "one is better".</h3>
<p><em>Looking for:</em> selling hours versus selling a product. The pay follows how the work multiplies, not how hard it is.</p>

<h3>5. What would you now say if an interviewer asked why you want to join their company?</h3>
<p><em>Looking for:</em> anything that names how they make money. If your answer would fit any company on earth, it is not an answer.</p>

<hr />

<h2>Where you are</h2>

<p>You now know what software is, what happens between your text file and the electricity, what kinds of company exist, and why they pay what they pay.</p>

<p>What you do not yet know is what people actually <em>do</em> all day inside these companies — who sits next to whom, how work arrives, who decides. That is the next section, and it is the part that self-taught developers most visibly lack in interviews. They can write a function and cannot say what a sprint is.</p>

<p>You are not going to be one of those people.</p>',
          'slug' => 'checkpoint-can-you-see-the-industry-yet',
        ),
      ),
    ),
    1 => 
    array (
      'title' => 'Part 2 — Inside a software company',
      'description' => 'Who does what, how work arrives, who to ask. The part self-taught developers most visibly lack.',
      'lessons' => 
      array (
        0 => 
        array (
          'title' => 'The teams, and what each one actually does',
          'duration' => 12,
          'excerpt' => 'Nine job families, described by what they do on a Tuesday afternoon rather than by their LinkedIn headline.',
          'content' => '<p>Job descriptions are written to sound impressive. This lesson is written to be accurate. For each team: what they actually do, and the moment you will first notice them.</p>

<p>We will keep building the exam portal, because it needs every one of these people.</p>

<h2>Engineering</h2>

<p><strong>What they do:</strong> write and change the code. Split, almost everywhere, into three:</p>

<ul>
	<li><strong>Frontend</strong> — what the student sees. The results page, the login form, the fact that it works on a ₹8,000 Android phone on a 3G connection. Harder than it sounds, and looked down on by people who have never done it.</li>
	<li><strong>Backend</strong> — the rules and the data. Calculating SGPA, deciding who has a backlog, making sure 4,000 simultaneous requests do not corrupt anybody\'s marks.</li>
	<li><strong>Full stack</strong> — both, at a level that varies enormously by company. In a startup it means both properly. In a job advert it sometimes means "we would like to pay one salary".</li>
</ul>

<h2>QA — Quality Assurance</h2>

<p><strong>What they do:</strong> find out what breaks, before a student does.</p>

<p>Not "clicking around". A good QA engineer on the exam portal asks: what happens if a student has no marks recorded? If a subject is deleted after grades are entered? If someone opens the results page twice at once? If the roll number contains a space?</p>

<p>They write automated tests too — code whose entire job is to run the other code and shout. QA is a genuine engineering discipline and a very real door into the industry, and it is dismissed mostly by people who have never watched a good tester take apart their work in four minutes.</p>

<h2>DevOps / SRE</h2>

<p><strong>What they do:</strong> the portal exists on an actual machine somewhere, and someone must own that.</p>

<p>Servers, deployments, monitoring, the alert at 2am when results day starts. When your code works on your laptop and not in production, this is the team you talk to — and the reason the whole industry now says "infrastructure as code" is that doing this by hand does not survive contact with a bad night.</p>

<h2>Product management</h2>

<p><strong>What they do:</strong> decide <em>what</em> gets built and in what order, then defend that decision.</p>

<p>The university wants SMS alerts, a mobile app, dark mode and a parent login. There is time for one this quarter. The PM decides which, tells the other three people no, and then explains why to everyone who is upset. They are not your boss; they are usually the person you will argue with most, and a good one is worth an enormous amount.</p>

<h2>Design</h2>

<p><strong>What they do:</strong> decide how it looks and, far more importantly, how it feels to use.</p>

<p>A designer is the reason a student under exam stress can find their result in one glance rather than three. If you have ever used a government portal, you have experienced the alternative.</p>

<h2>Data</h2>

<p><strong>What they do:</strong> answer questions the raw system cannot.</p>

<p>Which subjects have the highest failure rates? Did the new attendance rule change pass percentages? Data analysts and engineers turn "we think students struggle with Thermodynamics" into a number somebody can act on.</p>

<h2>Support</h2>

<p><strong>What they do:</strong> talk to the humans when it goes wrong.</p>

<p>They are the early warning system. Support saying "eleven students today say the marksheet PDF is blank" is the most valuable bug report in the building, and treating them as beneath you is one of the fastest ways to be quietly disliked.</p>

<h2>Sales, HR, Finance</h2>

<p><strong>Sales</strong> convinces the next university to buy. Their promises become your deadlines, which is worth understanding early. <strong>HR</strong> handles hiring, payroll and policy — they are the first humans you will meet, and they are not your adversary. <strong>Finance</strong> decides whether there is budget for another engineer, which is why headcount arguments take months.</p>

<h2>Why you were made to read all that</h2>

<p>Two reasons.</p>

<p>First, interviews. "Have you worked with QA before?" and "how would you handle a PM changing requirements mid-sprint?" are ordinary questions, and a candidate who has never heard of a PM answers them badly.</p>

<p>Second, and more usefully: one of these might be your way in. Every year, people who cannot get a developer role walk into QA, support engineering or data analysis — and two years later are developers, from the inside, with a salary the entire time. Nobody tells freshers this, because nobody tells freshers anything.</p>',
          'slug' => 'the-teams-and-what-each-one-actually-does',
        ),
        1 => 
        array (
          'title' => 'A day in the life, hour by hour',
          'duration' => 11,
          'excerpt' => 'Tuesday, at a company that runs the exam portal. Including the bit where results day goes wrong.',
          'content' => '<p>Most people picture this job as eight hours of typing in a dark room. It is not, and the difference matters, because a great deal of what gets you promoted happens in the parts that are not typing.</p>

<p>Here is a real shape of a Tuesday.</p>

<h2>9:40 — arrive, read, do not code</h2>

<p>Messages first. A tester has left a comment on the bug you fixed yesterday: it works, but only if the student has marks in every subject. Somebody with a backlog paper still gets a blank page.</p>

<p>They are right. Your fix was incomplete. This is normal, happens to everyone, and is not a referendum on you.</p>

<h2>10:00 — standup, fifteen minutes, standing up</h2>

<p>The whole team, briefly. Three sentences each: what I did yesterday, what I am doing today, what is blocking me.</p>

<p>The third one is the only one that matters, and it is the one freshers skip because saying "I am stuck" feels like admitting failure. It is not. A blocked engineer who says nothing for two days has cost the team two days; the one who says "I cannot get the test database to load" gets it fixed by 10:20.</p>

<h2>10:15 — actual work</h2>

<p>You open the backlog bug. You reproduce it first — create a test student with one missing subject, load the page, watch it break. Reproducing before fixing sounds obvious and is skipped constantly, and skipping it is how people "fix" things that were never broken.</p>

<p>The cause: the code assumes every student has a mark for every subject, and the university\'s own rules say that is not true. That committee argument from lesson one, resurfacing as a bug.</p>

<h2>11:30 — you have to ask a human</h2>

<p>Now a real question: what <em>should</em> a backlog subject show? A dash? "Pending"? The old failed mark? The code cannot tell you. The database cannot tell you. Only the university can.</p>

<p>So you ask the PM, who asks the university, and meanwhile you work on something else. Learning to park a blocked task instead of guessing is worth more than any framework you will learn this year.</p>

<h2>13:30 — code review</h2>

<p>You open a pull request: here is my change, please look before it goes near production.</p>

<p>A senior engineer comments: the fix works, but it queries the database inside a loop — one query per subject, so a student with eight subjects makes eight round trips. On results day, times four thousand students, that is the crash from lesson one, rebuilt by hand.</p>

<p>This is the single most valuable hour of your week for the first two years. Reviews feel like criticism and are actually the only free senior-engineer tutoring you will ever get.</p>

<h2>15:00 — you fix it and it merges</h2>

<p>One query, all subjects, joined properly. Tests pass. Approved. Merged. It will go out with tomorrow\'s release.</p>

<h2>16:20 — results day goes wrong</h2>

<p>An alert fires. The portal is responding slowly. Support says students are seeing timeouts.</p>

<p>Nobody panics and nobody starts guessing. Someone asks: what changed? A release went out at 15:45. It is rolled back — put yesterday\'s version back — and response times recover within four minutes.</p>

<p>Notice the order. <strong>Restore service first, understand it afterwards.</strong> The instinct to debug the broken thing while users are suffering is natural, human, and wrong.</p>

<h2>17:30 — the postmortem is scheduled</h2>

<p>Tomorrow, the team will work out what happened and how to make that class of failure impossible. In a healthy company nobody is named and nobody is blamed, because a culture where people hide mistakes is a culture where mistakes are found by customers.</p>

<h2>What the day was actually made of</h2>

<p>Roughly three hours of writing code. The rest was reading, asking, reviewing, and one small controlled emergency.</p>

<p>If that disappoints you, sit with it for a moment: it means you do not have to be the fastest typist in the room. The people who do well here are the ones who reproduce before fixing, ask instead of guessing, and say "I am blocked" on the same day they get blocked. All three are learnable this week.</p>',
          'slug' => 'a-day-in-the-life-hour-by-hour',
        ),
        2 => 
        array (
          'title' => 'How work arrives at your desk',
          'duration' => 9,
          'excerpt' => 'From "the university wants SMS alerts" to a task with your name on it. Every company runs some version of this pipeline.',
          'content' => '<p>Nobody walks over and says "please write some code today". Work arrives through a pipeline, and understanding it is what lets you answer the interview question "walk me through how a feature gets built" — which is asked constantly and answered badly.</p>

<h2>Stage 1 — somebody wants something</h2>

<p>The university registrar says: parents keep phoning the office on results day. Can students\' parents get an SMS when results are published?</p>

<p>That is a <strong>requirement</strong>. Note what it is not: it is not a design, and it is not a decision. It is a wish, expressed by someone who does not know what it costs.</p>

<h2>Stage 2 — somebody decides whether it is worth it</h2>

<p>The PM asks the questions nobody has: how many parents? Do we have their numbers? Consent to message them? What does an SMS cost, times 4,000, times two semesters? And what are we <em>not</em> building if we build this?</p>

<p>Most requests die here, and that is the system working. An engineering team that builds everything it is asked builds a swamp.</p>

<h2>Stage 3 — it becomes a ticket</h2>

<p>Approved work becomes a written item in a tracker — Jira, usually, whatever you may feel about it. A decent ticket has a description, an acceptance criterion ("a parent with a stored, consented number receives one SMS within 10 minutes of publication"), and an estimate.</p>

<p>Vague tickets are the single most common cause of wasted weeks. "Add SMS alerts" is not a ticket. It is a mood.</p>

<h2>Stage 4 — it gets scheduled into a sprint</h2>

<p>Teams work in fixed blocks, usually two weeks, called <strong>sprints</strong>. At the start, the team pulls in what it believes it can finish. At the end, that work should be genuinely done, not "done except testing".</p>

<p>The point of a fixed block is not speed. It is that every two weeks you find out whether you were right, instead of finding out after six months.</p>

<h2>Stage 5 — you build it</h2>

<p>You take the ticket. Branch, write the code, write tests, open a pull request, get reviewed, merge. Yesterday\'s lesson, in the small.</p>

<h2>Stage 6 — it is tested somewhere that is not production</h2>

<p>The change goes to <strong>staging</strong> — a copy of the real system with fake students. QA runs it there. Nobody\'s parents get a real SMS saying their child failed, because the numbers are not real.</p>

<h2>Stage 7 — it is released</h2>

<p>The change goes to production, where actual humans use it. Ideally to a small slice first, watched, then everyone. If something is wrong: roll back, exactly as in the previous lesson.</p>

<h2>Stage 8 — you find out if it worked</h2>

<p>Did calls to the registrar\'s office actually drop? If not, the feature failed even though the code works. This distinction — shipped versus <em>worked</em> — is the difference between an engineer and a senior engineer, and almost nobody talks to freshers about it.</p>

<h2>Say this in an interview</h2>

<p>"A requirement comes in, the PM decides whether it is worth building, it becomes a ticket with acceptance criteria, it goes into a sprint, I build it on a branch and get it reviewed, QA verifies it on staging, it is released — usually to a slice first — and then we check whether it actually solved the problem."</p>

<p>Say that as a fresher and you will sound like someone who has already worked. You now have, on paper, and paper is where interviews happen.</p>',
          'slug' => 'how-work-arrives-at-your-desk',
        ),
        3 => 
        array (
          'title' => 'Who you report to, and who you work with',
          'duration' => 8,
          'excerpt' => 'Team lead, manager, architect, scrum master, PM. Five titles, frequently confused, with very different power over your life.',
          'content' => '<p>In college there was a teacher and a head of department. At work there are five or six people with authority over different parts of your day, and they are not interchangeable. Asking the wrong one for the wrong thing is a small, avoidable friction that freshers hit constantly.</p>

<h2>Team lead</h2>

<p>A senior engineer who also guides the team. Reviews your code, decides technical approach, unblocks you. Usually still writes code.</p>

<p><strong>Go to them for:</strong> "I do not understand this code", "which approach should I take", "I am stuck". This is the person you should be talking to most, and — every single year, in every company — freshers stay silent for three days rather than admit confusion to them. Do not do that.</p>

<h2>Engineering manager</h2>

<p>Responsible for the <em>people</em>. Your leave, your appraisal, your salary, your growth, your promotion. Often does not write code any more.</p>

<p><strong>Go to them for:</strong> career, workload, money, anything about how work is affecting you. Not for a null pointer exception.</p>

<p>In smaller companies these first two are the same person wearing both hats. When that happens, notice which hat is on.</p>

<h2>Architect / principal engineer</h2>

<p>The person who decides how the big pieces fit — which database, how services talk, what happens when it must handle ten times the load. They set the shape you build inside.</p>

<p>You will not talk to them much in year one. Read what they write; it is the fastest available education.</p>

<h2>Scrum master / delivery manager</h2>

<p>Runs the process: sprints, standups, planning, retros. Chases blockers, keeps the board honest, protects the team from mid-sprint chaos.</p>

<p><strong>Go to them for:</strong> "this ticket is unclear", "I am blocked by another team". A good one makes the week smooth. A bad one adds meetings.</p>

<h2>Product manager</h2>

<p>Decides <em>what</em> is built and why. Not your boss — nothing about your salary or your leave — but a great deal about your work.</p>

<p><strong>Go to them for:</strong> "what should this actually do when a student has a backlog?" The question from Tuesday\'s lesson. That is a PM question, and taking it to your team lead just makes them ask the PM.</p>

<h2>The rule that saves you a year</h2>

<p><strong>Technical confusion goes to your lead. Product confusion goes to the PM. Anything about you goes to your manager.</strong></p>

<p>And one more, which is the real message of this whole section: none of these people can read your mind, and all of them would rather hear about a problem on day one than on day four. Every fresher believes staying quiet looks competent. It looks, from the other side of the table, exactly like a person who is stuck and hiding it — and everybody on that side of the table has been the person hiding it.</p>',
          'slug' => 'who-you-report-to-and-who-you-work-with',
        ),
        4 => 
        array (
          'title' => 'Checkpoint: could you survive week one?',
          'duration' => 6,
          'excerpt' => 'Six situations from a real first week. Say what you would do, and to whom.',
          'content' => '<p>Not a memory test. Each of these has happened to somebody in their first fortnight.</p>

<h3>1. Day two. You have been given a ticket and do not understand a word of the description.</h3>
<p><em>Good answer:</em> ask — same day. The ticket is unclear, so the scrum master or PM. Say it at standup: "I am blocked on understanding ticket 412." Nobody thinks less of you. Everybody thinks less of the person who says nothing until Friday.</p>

<h3>2. Your code works on your laptop. On staging it fails immediately.</h3>
<p><em>Good answer:</em> a rung of the ladder differs. Version, configuration, a file that only exists locally, data that only exists locally. Do not re-read your logic first — compare the environments.</p>

<h3>3. A reviewer leaves eleven comments on your first pull request.</h3>
<p><em>Good answer:</em> this is the job working correctly. Eleven comments on a fresher\'s first PR is normal and is free senior tutoring. Fix them, ask about the ones you do not understand, and do not apologise eleven times.</p>

<h3>4. Support says nine students report a blank marksheet. You are mid-ticket.</h3>
<p><em>Good answer:</em> tell your lead now. Nine real users beats your current task, and the decision is theirs, not yours. Bring the facts: how many, since when, which browser, what changed recently.</p>

<h3>5. The PM asks whether a change can be done by Friday. You genuinely do not know.</h3>
<p><em>Good answer:</em> "I do not know yet — give me two hours to look and I will tell you." Never guess a date to avoid an awkward pause. A guessed date becomes a promise the moment it leaves your mouth.</p>

<h3>6. You realise your merged change caused this afternoon\'s outage.</h3>
<p><em>Good answer:</em> say so immediately, out loud. Restore service first — roll back — and understand afterwards. In a healthy team you will be thanked, not blamed. In a team where you are blamed, you have learned something important about where you work.</p>

<hr />

<h2>Where you are</h2>

<p>You now know who does what, how work arrives, who to ask, and what a normal day looks like — including a normal bad one.</p>

<p>Next comes the vocabulary. Not because words matter for their own sake, but because every one of these terms will appear in a job description and in an interview, and not knowing them makes a capable person sound unprepared.</p>',
          'slug' => 'checkpoint-could-you-survive-week-one',
        ),
      ),
    ),
    2 => 
    array (
      'title' => 'Part 3 — The vocabulary',
      'description' => 'The words in every job advert and every interview, explained once, properly.',
      'lessons' => 
      array (
        0 => 
        array (
          'title' => 'Git, and why every job assumes you know it',
          'duration' => 12,
          'excerpt' => 'Not a tutorial. What version control is for, and the six commands that cover almost every day of your working life.',
          'content' => '<p>Every software job on earth assumes this and almost no Indian college teaches it. Learning it takes an afternoon. Not knowing it is visible within four minutes of an interview.</p>

<h2>The problem it solves</h2>

<p>You have done this. <code>project_final.doc</code>, then <code>project_final2.doc</code>, then <code>project_final_FINAL.doc</code>, then <code>project_final_FINAL_sir_edits.doc</code>. Then four of you emailed versions around and somebody\'s changes vanished.</p>

<p>Now imagine that with eight engineers, one exam portal, and a lost change meaning 4,000 students get the wrong marks. That is the problem. Git is the answer.</p>

<h2>What Git actually is</h2>

<p>A record of every version of every file, who changed it, when, and why — plus the ability to work on separate changes at once and combine them safely.</p>

<p>Three ideas do most of the work:</p>

<ul>
	<li><strong>A repository</strong> — the project, plus its entire history. Not a folder of files: a folder of files <em>and every state it has ever been in</em>.</li>
	<li><strong>A commit</strong> — one saved change with a message. "Fix blank marksheet for students with backlog subjects." A labelled point you can return to.</li>
	<li><strong>A branch</strong> — your own line of work, separate from everyone else\'s, until you deliberately merge it back.</li>
</ul>

<p>Branching is the part that feels magical when it lands. You and a colleague can change the same project at the same time without either of you losing anything.</p>

<h2>The six commands</h2>

<pre><code>git clone &lt;url&gt;        # get the project onto your machine, once
git checkout -b my-fix  # start a branch for what you are about to do
git add .               # stage the changes you want to save
git commit -m "..."     # save them, with a message explaining why
git push                # send your branch to the shared server
git pull                # get everyone else\'s work onto your machine</code></pre>

<p>That is genuinely most of it. There is a great deal more, and you will learn it when you need it, which is the correct time.</p>

<h2>Commit messages, briefly</h2>

<p>Write why, not what. The code already shows what changed.</p>

<p>Bad: <code>fix</code>. Also bad: <code>changes</code>, <code>update</code>, <code>asdf</code>. Good: <code>Fix blank marksheet when a student has an ungraded backlog subject</code>.</p>

<p>Six months from now, somebody — probably you — will be reading that message at 1am trying to work out why the portal is behaving strangely. Be kind to that person.</p>

<h2>Merge conflicts are not a disaster</h2>

<p>When two people change the same lines, Git refuses to guess and asks you. That is a merge conflict, and it terrifies beginners because the markers look like corruption.</p>

<p>It is not corruption. Git is showing you both versions and asking which is right — a question only a human can answer. Read both, keep what is correct, delete the markers, commit.</p>

<h2>Do this today</h2>

<p>Make a GitHub account. Create a repository. Push anything at all — even a text file with your name in it.</p>

<p>Do it now rather than "when I have a project worth showing", because a GitHub account created today with two years of small commits in it is worth considerably more at your first interview than one created the week you started applying. Recruiters look at the dates. Everybody\'s first repository is embarrassing; the only real mistake is starting it late.</p>',
          'slug' => 'git-and-why-every-job-assumes-you-know-it',
        ),
        1 => 
        array (
          'title' => 'Agile, sprint, standup, retro — the ritual vocabulary',
          'duration' => 9,
          'excerpt' => 'The words every job advert uses. What they mean, what they are for, and what they look like when a company does them badly.',
          'content' => '<p>You will be asked "have you worked in Agile?" You have not, and lying is a bad plan. But you can know exactly what it is, which is enough — and more than many people with two years of experience can manage.</p>

<h2>Where it came from</h2>

<p>Software used to be built the way a building is built: specify everything, plan for a year, build for two years, deliver, discover the customer wanted something else. This is called <strong>waterfall</strong>, and its failure mode is spectacular — a hundred person-years spent on the wrong thing.</p>

<p>Agile is the reaction: build a small piece, show somebody, find out you were wrong <em>now</em>, adjust. That is the entire idea. Everything below is machinery for doing that on a schedule.</p>

<h2>Sprint</h2>

<p>A fixed block of work, almost always two weeks. The team commits to a set of tickets, works, and at the end there is something genuinely finished.</p>

<p>The fixed length is the point. A deadline that moves teaches nobody anything; a deadline that arrives every fortnight teaches you very quickly how much your team can actually do.</p>

<h2>Standup</h2>

<p>Fifteen minutes, every morning, everyone. Did, doing, blocked.</p>

<p>Done well it takes twelve minutes and unblocks three people. Done badly it becomes a status report to a manager, everyone performs busyness, and nobody admits to being stuck. You will be able to tell which kind a company has within a week, and it tells you a great deal about the place.</p>

<h2>Sprint planning</h2>

<p>The meeting where the next fortnight is chosen and sized. Estimates are often in "points" rather than hours — a deliberate acknowledgement that humans are bad at predicting hours and slightly better at judging whether one job is bigger than another.</p>

<h2>Retrospective</h2>

<p>End of sprint: what went well, what did not, what we will change. No tickets, no status — just the team improving how it works.</p>

<p>This is the meeting that most reliably reveals a company\'s culture. If people say real things in the retro, it is a healthy team. If everyone says "communication could be better" and nothing changes for eleven sprints, you have learned something.</p>

<h2>Backlog and grooming</h2>

<p>The backlog is everything wanted but not yet built — the SMS alerts, the mobile app, dark mode, parent login. Grooming is the periodic tidy-up: clarify, size, reorder, and delete the things nobody will ever do.</p>

<h2>Kanban, in one paragraph</h2>

<p>The other common approach. No sprints; a continuous board — To do, In progress, In review, Done — with a limit on how many items may sit in each column. Common in support and operations teams, where work arrives when it arrives and cannot be scheduled a fortnight ahead.</p>

<h2>The honest bit</h2>

<p>Most companies claiming to be Agile are running standups and calling it Agile. The ceremonies are easy to copy; the actual idea — ship small, find out you are wrong, change — requires a willingness to be wrong in public that many organisations do not have.</p>

<p>Say that in an interview, carefully and without sneering, and you will sound like someone who has thought about how work works rather than someone who memorised a glossary. Which is, in fact, what you will have done.</p>',
          'slug' => 'agile-sprint-standup-retro-the-ritual-vocabulary',
        ),
        2 => 
        array (
          'title' => 'Dev, staging, production — the three worlds',
          'duration' => 8,
          'excerpt' => 'The most expensive lesson in software, available here for free instead of at 2am on results day.',
          'content' => '<p>There are three copies of the exam portal. Confusing them is how careers acquire stories.</p>

<h2>Development</h2>

<p>Your laptop. Fake students, fake marks, breaks constantly, and that is exactly what it is for. Nobody is harmed. Break it as hard and as often as you like — this is the only environment where being reckless is free.</p>

<h2>Staging</h2>

<p>A copy of the real system on real infrastructure, with data that resembles reality but is not. Same server setup, same database engine, same configuration, fake students.</p>

<p>Staging exists to catch the entire family of bugs that only appear away from your laptop: a missing configuration value, a database that behaves differently at volume, a file path that only exists in your home directory. QA tests here. Nothing you do here can hurt anybody.</p>

<h2>Production</h2>

<p>The real one. Real students, real marks, real parents, real consequences.</p>

<p>You do not experiment in production. You do not "just try something quickly" in production. You do not run a database update in production to see what happens — and the reason that sentence exists in every onboarding document ever written is that somebody, somewhere, has.</p>

<h2>The rule, and the reason for it</h2>

<p><strong>Changes flow one way: dev, then staging, then production.</strong> Never backwards, never skipping.</p>

<p>It is tempting to skip. It is Friday, the fix is one line, staging is slow, the change is obviously safe. And nine times out of ten it is. The tenth time, that one obviously-safe line meets some real-world condition that only exists in production — a student with no marks, a subject with a null credit value, four thousand people at once — and now you are explaining to a registrar why the results page shows everybody\'s marks as zero.</p>

<h2>Two words that will save you</h2>

<p><strong>Rollback:</strong> put the previous version back. It is the first move in almost every incident, and it is not an admission of failure — it is what restoring service looks like. Understand it afterwards, with the site working and nobody shouting.</p>

<p><strong>Hotfix:</strong> a small, urgent change that goes out immediately rather than waiting for the normal release. Necessary sometimes, dangerous always, because it skips exactly the checks that exist to catch mistakes made in a hurry — and a hurry is precisely when mistakes are made.</p>

<h2>Why this is in an orientation module</h2>

<p>Because "have you deployed to production?" is a question freshers get, and because your first week somewhere will involve someone saying "do not touch prod" as if you already know what that means.</p>

<p>Now you do. There are three worlds, changes flow one way, and the third one has real people in it.</p>',
          'slug' => 'dev-staging-production-the-three-worlds',
        ),
      ),
    ),
    3 => 
    array (
      'title' => 'Part 4 — Roles, money and experience',
      'description' => 'What each job actually pays, how hard each door is, and how the experience trap is really broken.',
      'lessons' => 
      array (
        0 => 
        array (
          'title' => 'The roles, honestly',
          'duration' => 14,
          'excerpt' => 'Ten jobs: what you actually do, what it pays a fresher in India, how hard it is to get in, and how easy it is to leave.',
          'content' => '<p>Every roadmap on the internet describes roles by their skill list. That tells you nothing about whether you would like the job. Here is each one by what you do on a Tuesday, what it pays, and — the number nobody publishes — how hard the door is.</p>

<p>Salary figures are broad ranges for freshers in Indian metros, and they move. Treat them as shape, not gospel, and check them against real openings before you make a decision.</p>

<h2>Backend engineer</h2>
<p><strong>Tuesday:</strong> writing the rules and the data handling. Why is the SGPA wrong for backlog students, why is this endpoint slow, how do we not corrupt marks when 4,000 people load at once.<br />
<strong>Needs:</strong> one language properly, databases, APIs, DSA for interviews.<br />
<strong>Fresher pay:</strong> ₹3–6 LPA services, ₹8–20 LPA product.<br />
<strong>Door:</strong> the widest of all. Most openings, most competition, best-understood path.</p>

<h2>Frontend engineer</h2>
<p><strong>Tuesday:</strong> building what students see, and making it work on a cheap phone and a bad connection.<br />
<strong>Needs:</strong> HTML, CSS, JavaScript properly, then React.<br />
<strong>Fresher pay:</strong> ₹3–6 LPA services, ₹7–18 LPA product.<br />
<strong>Door:</strong> wide, and it is the fastest route from zero to something visible you can show — which matters enormously when you have no experience and need a portfolio.</p>

<h2>Full stack engineer</h2>
<p><strong>Tuesday:</strong> both, badly split by whoever is shouting loudest.<br />
<strong>Fresher pay:</strong> ₹3.5–7 LPA services, ₹8–20 LPA product.<br />
<strong>Door:</strong> wide, especially in startups. Be alert: in some job adverts "full stack" means "we would like one salary for two jobs".</p>

<h2>QA / automation engineer</h2>
<p><strong>Tuesday:</strong> designing how to break things, and writing code that tests other code.<br />
<strong>Needs:</strong> a language, Selenium or Playwright, and genuine suspicion.<br />
<strong>Fresher pay:</strong> ₹3–5 LPA services, ₹6–14 LPA product.<br />
<strong>Door:</strong> noticeably easier than development, and — this is the part nobody tells freshers — it is inside the same building. Automation QA is engineering. Many developers started here.</p>

<h2>DevOps / SRE</h2>
<p><strong>Tuesday:</strong> deployments, monitoring, servers, and being the person who is called at 2am.<br />
<strong>Needs:</strong> Linux, networking, cloud, scripting.<br />
<strong>Fresher pay:</strong> ₹4–7 LPA services, ₹9–20 LPA product.<br />
<strong>Door:</strong> harder as a pure fresher — most people arrive after a year or two elsewhere. Very well paid once you are in, and on-call is a real cost to a real life.</p>

<h2>Data analyst</h2>
<p><strong>Tuesday:</strong> SQL, spreadsheets, dashboards, and answering "which subjects fail most, and did the new rule change anything".<br />
<strong>Needs:</strong> SQL properly, Excel, a visualisation tool, and the ability to explain a number to someone who does not like numbers.<br />
<strong>Fresher pay:</strong> ₹3.5–7 LPA.<br />
<strong>Door:</strong> quite open, and unusually friendly to non-CS graduates. Also the most common route into data engineering, which pays considerably more.</p>

<h2>Data engineer</h2>
<p><strong>Tuesday:</strong> building the pipelines that move data from where it happens to where it can be analysed.<br />
<strong>Fresher pay:</strong> ₹5–9 LPA services, ₹10–22 LPA product.<br />
<strong>Door:</strong> harder direct from college; a very common second job after analyst or backend.</p>

<h2>Mobile engineer</h2>
<p><strong>Tuesday:</strong> the Android or iOS app, plus a category of problems web developers never face — the app must work on a phone from 2019 with no signal in a lift.<br />
<strong>Fresher pay:</strong> ₹3.5–7 LPA services, ₹8–18 LPA product.<br />
<strong>Door:</strong> moderate. Fewer openings than web, and correspondingly fewer applicants.</p>

<h2>Support / implementation engineer</h2>
<p><strong>Tuesday:</strong> talking to the universities using the portal, diagnosing what is wrong, fixing configuration, escalating real bugs.<br />
<strong>Fresher pay:</strong> ₹2.5–5 LPA.<br />
<strong>Door:</strong> the most open in this list, and consistently underrated. You learn the product faster than anyone and you learn to talk to humans under pressure — two things that make an internal move to engineering realistic within eighteen months.</p>

<h2>Business analyst</h2>
<p><strong>Tuesday:</strong> sitting between the university and the engineers, turning "parents keep phoning us" into something buildable.<br />
<strong>Fresher pay:</strong> ₹3.5–7 LPA.<br />
<strong>Door:</strong> open, and genuinely rewards good English and clear writing more than code.</p>

<h2>How to read this list</h2>

<p>Two honest observations.</p>

<p>First: the highest-paying door is not the one you should aim at if it is closed to you today. A ₹3 LPA support role that you can get in March beats a ₹14 LPA product role that you cannot get at all — because in eighteen months the first one has made you an experienced hire and the second one is still a wish.</p>

<p>Second: none of these choices is permanent. Every one of them can move to any other within about two years, and people do it constantly. Choose the open door, learn from inside, move deliberately. That is not settling. That is the actual strategy that works, and it is the one nobody explains to a final-year student.</p>',
          'slug' => 'the-roles-honestly',
        ),
        1 => 
        array (
          'title' => 'Money, honestly',
          'duration' => 11,
          'excerpt' => 'What freshers really earn, what CTC hides, what actually raises a salary, and why a low first offer is not a verdict on you.',
          'content' => '<p>Nobody in your life will discuss this number plainly. Colleges quote their best placement as if it were typical. The internet posts screenshots of ₹40 LPA offers. Your relatives have opinions based on a cousin in 2011.</p>

<p>So, plainly.</p>

<h2>What CTC actually means</h2>

<p>CTC is Cost To Company — everything they spend on you, not what reaches your bank account. A ₹4.5 LPA CTC typically breaks down roughly like this:</p>

<ul>
	<li>Basic and allowances — the real monthly salary</li>
	<li>Employer provident fund contribution — real money, but locked away</li>
	<li>Gratuity — which you only receive after five years of service</li>
	<li>Insurance premiums — a genuine benefit you never see</li>
	<li>A performance bonus, often described as if it were guaranteed</li>
	<li>Sometimes a joining bonus, counted in year one only, occasionally with a clawback if you leave early</li>
</ul>

<p>A ₹4.5 LPA CTC often lands as roughly ₹28,000–32,000 a month in hand. That is not a scam, but it is a number you should compute <em>before</em> accepting, not after your first payslot arrives and disappoints you.</p>

<p><strong>Ask for the full breakup in writing before you accept.</strong> Every legitimate employer will send it. Any reluctance is itself information.</p>

<h2>Realistic fresher ranges</h2>

<table>
	<tr><th>Where</th><th>Typical fresher CTC</th></tr>
	<tr><td>Large service companies, mass hiring</td><td>₹3.2–4.5 LPA</td></tr>
	<tr><td>Service companies, specialised or digital roles</td><td>₹4.5–7 LPA</td></tr>
	<tr><td>Small and mid product companies</td><td>₹6–12 LPA</td></tr>
	<tr><td>Global capability centres</td><td>₹8–16 LPA</td></tr>
	<tr><td>Top-tier product companies</td><td>₹15–35 LPA</td></tr>
	<tr><td>Early startups</td><td>₹4–12 LPA, plus equity that is usually worth nothing</td></tr>
</table>

<p>Two things that are true at once: the bottom of that table is a real, respectable job that thousands of good engineers start in, and the top of it is reachable within a few years from the bottom.</p>

<h2>What actually raises a salary</h2>

<p>Ranked by how much they move the number, which is not the order people expect.</p>

<ol>
	<li><strong>Changing company.</strong> Uncomfortable and true. An internal appraisal is typically 5–15%. A well-timed switch after two years is commonly 40–80%. Companies pay market rate to hire and inertia rate to retain.</li>
	<li><strong>Moving from services to product.</strong> Often the single largest jump available to a fresher, and it is exactly what the second and third years are for.</li>
	<li><strong>Scarce skills.</strong> Whatever is in demand and short supply this year.</li>
	<li><strong>Interviewing well.</strong> Two people with identical skill can end up 30% apart because one of them practised. This is a learnable skill and it is a whole module here.</li>
	<li><strong>Negotiating.</strong> Ten minutes of discomfort, frequently ₹50,000–₹200,000 a year, permanently, compounding into every future offer.</li>
	<li><strong>Staying loyal and working hard.</strong> Last on the list. It should be higher. It is not.</li>
</ol>

<h2>The first offer is a door, not a verdict</h2>

<p>Here is the thing this platform was built to say.</p>

<p>The person who built it took ₹13,000 a month — ₹1.8 LPA — after thirty-three rejections. That was not a good salary in 2017 and it is not one now. But it converted "unemployed graduate" into "software engineer with a job", and that conversion is the expensive one. Three months later, having treated the job hunt as a subject worth studying rather than a thing that happens to you, the next offer was several times larger.</p>

<p>The fresher tag is a door that opens once. What you earn walking through it matters much less than the fact that you are through, because from the inside every subsequent number is negotiable and every subsequent move is easier.</p>

<p>Take the door. Then read the Offer &amp; Switch module, and stop taking the first number you are offered.</p>',
          'slug' => 'money-honestly',
        ),
        2 => 
        array (
          'title' => 'What "experience" actually means',
          'duration' => 8,
          'excerpt' => 'Every job wants experience. You need experience to get a job. Here is how that loop is actually broken, by thousands of people every year.',
          'content' => '<p>"Minimum 2 years experience." You have none. Everyone has told you this is a catch-22 and left you there. It is not, and the way out is specific.</p>

<h2>What they are actually asking for</h2>

<p>Nobody wants "two years" as a quantity of time. Time is not a skill. What that line is a proxy for is:</p>

<ul>
	<li>You have worked on code you did not write and cannot fully understand</li>
	<li>You have shipped something real people used, and dealt with it breaking</li>
	<li>You can work with other people — reviews, disagreements, deadlines</li>
	<li>You will not need supervision for every task</li>
	<li>You have been wrong in public and survived it</li>
</ul>

<p>Every one of those can be demonstrated without two years of employment. Not equally well — but well enough to get an interview, and the interview is the whole battle.</p>

<h2>Four legitimate substitutes</h2>

<p><strong>A real project that other people use.</strong> Not a tutorial clone. Something with actual users, even fifteen of them. Build the attendance tracker your own department needs and get one lecturer to use it, and you now have: real users, real bug reports, real deployment, real "it worked on my laptop". That is a genuine interview story and almost nobody has one.</p>

<p><strong>Open source contributions.</strong> The purest form of "code you did not write". Find a project, read its issues, fix a small one, survive the review. A merged pull request in a real repository is verifiable evidence you can work inside somebody else\'s codebase — which is exactly what the two-year line is asking about.</p>

<p><strong>Internships, including unglamorous ones.</strong> Three months at a company nobody has heard of still counts, on a CV and in your head.</p>

<p><strong>Freelance work.</strong> One paying client — a shop, a coaching centre, a friend\'s business — teaches requirements, scope changes, deadlines and invoicing all at once.</p>

<h2>The number that matters</h2>

<p>The gap between graduating and your first job is the most expensive thing on your CV, and it grows.</p>

<p>At three months, nobody asks. At eight months, they ask and accept a good answer. At eighteen months with nothing to show, you are competing against this year\'s graduates who are cheaper, and the question becomes hard to answer well.</p>

<p>Which is the whole argument for taking the open door. A ₹3 LPA support role in April is not a compromise of your ambitions — it stops the clock, and the clock is the thing that actually damages careers.</p>

<h2>What to do this week</h2>

<p>Pick the small system you wrote down in the first lesson — the thing your college does badly. You are going to build it across this platform\'s modules, and by the end you will have a real project, with real users, deployed somewhere real, with a Git history that shows a year of consistent work.</p>

<p>That is not a substitute for experience. In every way that an interviewer can actually verify, it <em>is</em> experience.</p>',
          'slug' => 'what-experience-actually-means',
        ),
      ),
    ),
    4 => 
    array (
      'title' => 'Part 5 — Choosing a direction',
      'description' => 'A first guess, deliberately not a decision.',
      'lessons' => 
      array (
        0 => 
        array (
          'title' => 'Role reality check: what do you actually want to do?',
          'duration' => 10,
          'excerpt' => 'Not a personality quiz. Six honest questions about what you enjoy, and what each answer points at.',
          'content' => '<p>Answer for what you actually enjoy, not what you think pays best or what your friends are doing. Nobody sees this, and lying to yourself here costs a year.</p>

<h3>1. Something is broken and nobody knows why. How do you feel?</h3>
<p><strong>Energised, want to dig in</strong> → backend, DevOps, QA. Debugging is genuinely the core of these jobs, and people who enjoy it are rare and valuable.<br />
<strong>Anxious, want somebody to just tell you</strong> → this is normal at the start and it fades with competence. Notice whether it fades. If after a year of practice it has not, frontend, analysis or business analysis will suit you better than being on call.</p>

<h3>2. You have built something that works. What do you want next?</h3>
<p><strong>Show it to somebody</strong> → frontend, mobile, product. You care about the thing being used.<br />
<strong>Make it faster or cleaner</strong> → backend, data engineering, infrastructure. You care about the thing being right.</p>

<h3>3. A page looks wrong by eight pixels. Be honest.</h3>
<p><strong>It bothers you</strong> → frontend, design. This is not a small trait; it is most of the job.<br />
<strong>You genuinely cannot see it</strong> → stay away from frontend. This is not a failing. It is a fact about your attention, and fighting it for forty years is miserable.</p>

<h3>4. Explaining something technical to a non-technical person:</h3>
<p><strong>Enjoyable</strong> → business analysis, product, support engineering, solutions engineering. These pay well, are chronically short of people, and are wide open to good communicators.<br />
<strong>Draining</strong> → deep engineering roles. Perfectly legitimate. Just do not take a client-facing job for the salary and then resent it.</p>

<h3>5. Which annoys you more?</h3>
<p><strong>A system that is slow</strong> → performance, backend, infrastructure.<br />
<strong>A system that is confusing</strong> → frontend, design, product.<br />
<strong>A system nobody can explain the numbers for</strong> → data.</p>

<h3>6. Two years from now, what would make you feel it went well?</h3>
<p><strong>"I understand how things work"</strong> → depth. Backend, systems, infrastructure.<br />
<strong>"People use what I made"</strong> → product-facing. Frontend, mobile, product.<br />
<strong>"I earn considerably more"</strong> → entirely valid, and it points at strategy over role: get in anywhere, get good, switch deliberately at two years.</p>

<hr />

<h2>Now the important part</h2>

<p>Whatever you concluded — <strong>do not treat it as a decision</strong>. Treat it as a first guess that tells you which language and which projects to start with.</p>

<p>Changing direction in this field costs weeks, not years. Frontend to backend: a few months. QA to development: about a year, from inside a job, while being paid. Support to engineering: routine, and companies prefer it because you already know the product.</p>

<p>The genuinely expensive mistake is not choosing wrong. It is not choosing at all — spending eleven months reading roadmaps, comparing languages, and waiting for certainty that does not exist for anybody.</p>

<h2>What happens next</h2>

<p>You now know what software is, how the industry is shaped, who does what, what things are called, what the jobs pay, and roughly which direction suits you.</p>

<p>Next comes the foundation: operating systems, databases, networks, object-oriented programming and data structures. Then one language, properly. Then projects, then the job search itself as a subject in its own right.</p>

<p>No mathematics. That was never the barrier, whatever anyone told you.</p>

<p>You were never bad at this. Nobody gave you the order. You have it now.</p>',
          'slug' => 'role-reality-check-what-do-you-actually-want-to-do',
        ),
      ),
    ),
  ),
);
