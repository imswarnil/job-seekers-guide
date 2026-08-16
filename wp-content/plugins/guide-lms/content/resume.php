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
  'slug' => 'resume-linkedin-mastery',
  'title' => 'Résumé & LinkedIn Mastery',
  'code' => 'JSG-101',
  'level' => 'beginner',
  'header' => 'minimal',
  'tier' => 'free',
  'menu_order' => 1,
  'excerpt' => 'Your résumé gets six seconds from a human and a parse attempt from a machine before that. This is how to survive both, and how to be findable on LinkedIn when recruiters search.',
  'content' => '<p>Most résumé advice is written for people who already have jobs. This is written for the six-second scan, the applicant tracking system that reads your file before any human does, and the LinkedIn search results page you are competing in without knowing it.</p><p>Nothing here is about being impressive. It is about not being filtered out for reasons that have nothing to do with whether you can do the work — which is how most good candidates disappear.</p>',
  'outcomes' => 
  array (
    0 => 'Build a one-page résumé that survives both an ATS parser and a six-second human scan',
    1 => 'Turn duty-style bullets into evidence with numbers you actually have',
    2 => 'Write a LinkedIn headline containing the words recruiters search for',
    3 => 'Set up Open to Work so you appear in recruiter searches',
  ),
  'requirements' => 
  array (
    0 => 'An existing résumé, however bad',
    1 => 'A LinkedIn account',
  ),
  'sections' => 
  array (
    0 => 
    array (
      'title' => 'Resume Foundations',
      'description' => 'The document itself: what gets looked at, what gets parsed, and what to delete.',
      'lessons' => 
      array (
        0 => 
        array (
          'title' => 'The six-second recruiter scan',
          'duration' => 8,
          'excerpt' => 'Your résumé is not read. It is scanned, in about six seconds, by someone with 300 more to get through. Design for that.',
          'content' => '<p>You spent a weekend on your résumé. It gets six seconds.</p>

<p>That is not cruelty. A recruiter hiring two freshers may receive 400 applications. Reading each properly at four minutes apiece is twenty-six hours of work for one small role. So they do not read. They scan — and either something catches, or the tab closes.</p>

<h2>What happens in those six seconds</h2>

<p>Eye-tracking studies of recruiters are remarkably consistent. In roughly this order, they look for:</p>

<ol>
	<li>Your name and the role you are aiming at</li>
	<li>Your most recent thing — job, internship, or project</li>
	<li>How long you did it</li>
	<li>Your education, briefly</li>
	<li>Skills, scanned for keywords rather than read</li>
</ol>

<p>Notice what is missing: your objective statement, your hobbies, your declaration, your father\'s name, and the passport-size photograph in the corner. Nobody looks at these. Two of them actively cost you space.</p>

<h2>The test</h2>

<p>Here is a test you can run today, and it is uncomfortable.</p>

<p>Give your résumé to a friend. Let them look for six seconds — count it out loud — then take it away and ask three questions: <em>What role am I applying for? What is the most impressive thing I have done? Where did I study?</em></p>

<p>If they cannot answer all three, your résumé does not fail because of your experience. It fails because of its layout, and layout is entirely within your control this afternoon.</p>

<h2>What this means concretely</h2>

<p><strong>One page.</strong> You are a fresher. Two pages does not signal depth, it signals that you could not decide what mattered.</p>

<p><strong>Top third is everything.</strong> Name, target role, contact, and your single strongest item. If your best project is at the bottom of page one, it may as well not exist.</p>

<p><strong>No photo, no age, no marital status, no father\'s name.</strong> Standard on Indian résumé templates, useless everywhere, and it consumes the most valuable space on the page.</p>

<p><strong>Delete the objective.</strong> "Seeking a challenging role in a reputed organisation where I can utilise my skills" appears on tens of thousands of résumés and tells a recruiter nothing at all. If you want a line at the top, make it specific: <em>"Final-year CS student. Built and deployed an attendance system used by 3 departments. Looking for a backend role."</em></p>

<h2>The uncomfortable reframe</h2>

<p>Your résumé is not an autobiography and it is not a record of your worth. It is a six-second advertisement whose only job is to earn a fifteen-minute phone call.</p>

<p>That is genuinely all it has to do. Once you accept that, most decisions become easy — every element either helps get the call or is taking up room.</p>

<h2>Do this now</h2>

<p>Open your current résumé. Delete the objective, the photo, and every personal detail that is not a way of contacting you. You have just recovered about a fifth of the page, and it is the fifth that gets looked at.</p>',
          'slug' => 'the-six-second-recruiter-scan',
        ),
        1 => 
        array (
          'title' => 'Formatting that survives an ATS',
          'duration' => 9,
          'excerpt' => 'Before a human scans it, software parses it. Here is what that software cannot read, and why beautiful résumés get rejected unseen.',
          'content' => '<p>Between you and the six-second scan there is usually a machine. An Applicant Tracking System takes your file, tries to pull structured fields out of it — name, skills, employers, dates — and stores the result.</p>

<p>If it cannot parse your file, a human may never see it. Not rejected: never displayed. This is the single most common invisible failure in a job hunt, and it is entirely preventable.</p>

<h2>What breaks a parser</h2>

<ul>
	<li><strong>Tables.</strong> The most common cause. A résumé laid out in a table often parses as gibberish, with your skills interleaved with your dates.</li>
	<li><strong>Multiple columns.</strong> That elegant sidebar with your skills reads, to a parser, as one continuous run of words mixed into your work history.</li>
	<li><strong>Text inside images.</strong> Anything in a graphic is invisible. A skills chart with little five-star ratings contributes exactly nothing.</li>
	<li><strong>Headers and footers.</strong> Frequently ignored entirely. Put your phone number in the footer and you may not have supplied a phone number.</li>
	<li><strong>Unusual fonts and icons.</strong> Icon fonts often extract as random characters, which is how you end up with "" next to your email address.</li>
	<li><strong>Creative section names.</strong> "My Journey" instead of "Experience". The parser is looking for the standard words.</li>
</ul>

<h2>What works</h2>

<p>A boring, single-column document. Genuinely.</p>

<ul>
	<li>One column, top to bottom</li>
	<li>Standard headings: <strong>Experience</strong>, <strong>Education</strong>, <strong>Projects</strong>, <strong>Skills</strong></li>
	<li>A standard font — Calibri, Arial, Georgia, Times</li>
	<li>Plain bullet points, no icons</li>
	<li>Dates in a consistent format: <em>Jun 2024 – Aug 2024</em></li>
	<li>Contact details in the body, not the header</li>
	<li>Saved as PDF, unless the application explicitly asks for .docx</li>
</ul>

<h2>Test it in thirty seconds</h2>

<p>Open your PDF. Select all, copy, paste into a plain text editor.</p>

<p>What you see is roughly what the parser sees. If it is readable and in the right order, you are fine. If your skills have landed in the middle of your education, you have just found out why the applications went quiet.</p>

<p>Do this before your next application. It takes thirty seconds and it is the highest-value thirty seconds in this entire course.</p>

<h2>The trap</h2>

<p>Search "resume template" and you will find beautiful two-column designs with sidebars, skill meters and colour blocks. They are designed to be admired, and they are designed by people who have never watched one go through a parser.</p>

<p>For a design or marketing role at a small studio where a human opens every file, use one. For a fresher applying to companies that receive four hundred applications through a portal, the plain document beats the beautiful one every single time — not because it looks better, but because it exists as far as the machine is concerned.</p>',
          'slug' => 'formatting-that-survives-an-ats',
        ),
        2 => 
        array (
          'title' => 'Bullet points that actually say something',
          'duration' => 11,
          'excerpt' => 'Most résumé bullets describe duties. Duties are forgettable. Here is the structure that turns them into evidence.',
          'content' => '<p>Here are two bullets describing exactly the same work.</p>

<blockquote><p>Worked on the college attendance system using Java and MySQL.</p></blockquote>

<blockquote><p>Built an attendance system in Java and MySQL that replaced paper registers for 3 departments, cutting the time staff spent on attendance from about 40 minutes a day to under 5.</p></blockquote>

<p>Same project. Same student. The first one is a duty. The second one is evidence — and it is the only one that produces a follow-up question in an interview.</p>

<h2>The structure</h2>

<p>Every strong bullet has three parts:</p>

<p><strong>What you did</strong> (an active verb) + <strong>how you did it</strong> (the technology) + <strong>what changed as a result</strong> (a number, if you possibly can).</p>

<p>The third part is the one everyone omits, and it is the one that matters. It is the difference between claiming you can do something and demonstrating that you did.</p>

<h2>"But I have no numbers"</h2>

<p>You do. People think numbers mean revenue and users. They mean anything countable, and you have been surrounded by countable things:</p>

<ul>
	<li>How many people used it? (3 departments. 60 students. My whole class.)</li>
	<li>How much time did it save? (40 minutes a day. Two hours a week.)</li>
	<li>How much faster did it get? (Page load from 4 seconds to under 1.)</li>
	<li>How much did you build? (12 screens. 8 API endpoints. 4,000 lines.)</li>
	<li>How long did it take? (Three weeks. One semester.)</li>
	<li>How many did you handle? (Marks for 400 students across 6 subjects.)</li>
</ul>

<p>Estimate honestly if you must, and be ready to explain the estimate. "About 40 minutes, because there were four classes a day and it took roughly ten minutes each" is a perfectly good answer, and the fact that you thought about it at all puts you ahead.</p>

<h2>Verbs that carry weight</h2>

<p>Start every bullet with a verb, and prefer ones that imply you made a decision: <em>built, designed, automated, reduced, migrated, integrated, debugged, deployed, rewrote, tested</em>.</p>

<p>Avoid the passive ones that describe proximity rather than action: <em>worked on, was involved in, helped with, was responsible for, participated in</em>. "Was involved in" is what you write when you did not do very much, and every recruiter knows it.</p>

<h2>Rewrite these</h2>

<p>Try it before reading the answers.</p>

<p><strong>Before:</strong> "Worked on a college website using HTML and CSS."<br />
<strong>After:</strong> "Built and deployed the department\'s event website in HTML and CSS; used by roughly 200 students for fest registration and cut manual form collection entirely."</p>

<p><strong>Before:</strong> "Learned Python and did some projects."<br />
<strong>After:</strong> "Wrote a Python script that scrapes and compares 5 job portals daily, replacing about an hour of manual searching."</p>

<p><strong>Before:</strong> "Was part of the technical team of the college fest."<br />
<strong>After:</strong> "Ran registration systems for a 3-day fest with 1,200 attendees; handled the payment reconciliation for 340 paid entries."</p>

<h2>The exercise</h2>

<p>Take every bullet on your résumé. For each, ask: <em>what changed because I did this, and can I count it?</em></p>

<p>Some bullets will not survive. Delete them. A résumé with four bullets that mean something beats one with eleven that do not, and you have six seconds either way.</p>',
          'slug' => 'bullet-points-with-the-xyz-formula',
        ),
        3 => 
        array (
          'title' => 'Checkpoint: is your résumé ready to send?',
          'duration' => 6,
          'excerpt' => 'Twelve checks. Do all of them before you apply anywhere — failing any one of them can end an application invisibly.',
          'content' => '<p>Open your actual résumé and go through this honestly. Nobody is watching.</p>

<h3>Structure</h3>
<ul>
	<li>One page</li>
	<li>Single column, no tables anywhere</li>
	<li>Standard headings — Experience, Projects, Education, Skills</li>
	<li>Contact details in the body, not in the header or footer</li>
</ul>

<h3>Content</h3>
<ul>
	<li>No objective statement, no photo, no date of birth, no father\'s name, no declaration</li>
	<li>The strongest thing you have done is in the top third of the page</li>
	<li>Every bullet starts with an active verb</li>
	<li>At least half your bullets contain a number</li>
	<li>Nothing says "worked on", "was involved in" or "responsible for"</li>
</ul>

<h3>Mechanics</h3>
<ul>
	<li>Copy-pasted into a plain text editor and it is still readable, in order</li>
	<li>Saved as PDF, named sensibly — <code>Swarnil-Singhai-Backend-Engineer.pdf</code>, not <code>resume final2.pdf</code></li>
	<li>Read aloud once, start to finish, for typos — reading aloud catches what your eyes skim over</li>
</ul>

<hr />

<h2>The six-second test, one more time</h2>

<p>Hand it to someone. Six seconds. Take it away. Ask: what role, what is the best thing they did, where did they study?</p>

<p>If they get all three, you are ready to apply.</p>

<h2>A note before you go</h2>

<p>A perfect résumé does not get you a job. It gets you a phone call, and it stops you being filtered out for reasons that have nothing to do with your ability.</p>

<p>That is worth two hours of work, and it is worth doing once, properly, rather than tinkering with it every week for four months. Finish it, then go and spend your time on the things that actually move the needle: referrals, projects, and practising the interview.</p>',
          'slug' => 'checkpoint-resume-fundamentals',
        ),
      ),
    ),
    1 => 
    array (
      'title' => 'LinkedIn That Gets Found',
      'description' => 'Being findable when a recruiter searches, which is the half nobody optimises.',
      'lessons' => 
      array (
        0 => 
        array (
          'title' => 'A headline and photo that pass the filter',
          'duration' => 8,
          'excerpt' => 'Recruiters search LinkedIn and see a list. Your photo and headline are the whole of your first impression — everything else is behind a click.',
          'content' => '<p>Recruiters do not browse LinkedIn. They search it — "Java fresher Pune", "React developer Hyderabad" — and get a list of names, photos and one line of text each.</p>

<p>That list is the competition. Your entire first impression is a photo and a headline, and most freshers waste both.</p>

<h2>The photo</h2>

<p>Not optional. Profiles with a photo get dramatically more views, and a profile without one reads as abandoned.</p>

<p>It does not need a photographer. It needs:</p>

<ul>
	<li>Your face taking up most of the frame, not a distant full-body shot</li>
	<li>Reasonable light — stand facing a window, in the day</li>
	<li>A plain background, or at least a boring one</li>
	<li>Something you would wear to an interview</li>
	<li>A neutral, pleasant expression. You do not have to grin.</li>
</ul>

<p>What to avoid: sunglasses, cropped group photos with someone\'s shoulder still in frame, heavy filters, and your college ID photo, which was taken under a tube light on the worst day of your year.</p>

<p>Ten minutes and a friend with a phone is genuinely enough.</p>

<h2>The headline</h2>

<p>By default LinkedIn writes "Student at [College]". Thousands of people have that headline. It says nothing about what you can do and nothing about what you want.</p>

<p>The headline has one job: contain the words a recruiter is searching for, and say what you are aiming at.</p>

<p><strong>Weak:</strong> Student at XYZ Institute of Technology<br />
<strong>Weak:</strong> Aspiring Software Developer | Passionate about coding | Fast learner<br />
<strong>Strong:</strong> Final-year CS student | Java, Spring Boot, MySQL | Looking for backend developer roles<br />
<strong>Strong:</strong> Frontend developer — React, JavaScript | Built and deployed 3 live projects | Open to opportunities in Bengaluru</p>

<p>The pattern: <em>what you are</em>, <em>the technologies by name</em>, <em>what you want</em>. The technologies matter most, because they are literally what gets typed into the search box.</p>

<h2>Say "passionate" less</h2>

<p>"Passionate about technology", "hard-working", "quick learner", "team player". Every single person applying has written these. They are unverifiable and therefore weightless.</p>

<p>Replace them with something checkable. "Built 3 projects, 2 deployed and live" is worth more than every adjective on the page, because a recruiter can click and see.</p>

<h2>Fifteen minutes, today</h2>

<p>Change your photo. Rewrite your headline using the pattern above, with the actual technology names you know. Then search LinkedIn for your own target role in your own city and look at where you appear in the list.</p>

<p>That list is the thing you are competing in. It is much easier to move up it than most people assume, because most people never try.</p>',
          'slug' => 'headline-and-photo-that-pass-the-filter',
        ),
        1 => 
        array (
          'title' => 'An About section people actually finish',
          'duration' => 9,
          'excerpt' => 'Three short paragraphs, written in first person, containing the words that get searched. Not an essay, and not a third-person biography.',
          'content' => '<p>The About section is where most profiles die. It is either empty, or it is four hundred words of "I am a highly motivated individual seeking to leverage my skills in a dynamic organisation".</p>

<p>Nobody finishes reading that. Here is what works.</p>

<h2>Three paragraphs</h2>

<p><strong>One — who you are and what you are aiming at.</strong> Two sentences, plain, first person.</p>

<blockquote><p>I am a final-year computer science student at a college in Nagpur, and I build backend systems in Java. I am looking for my first full-time role as a backend developer.</p></blockquote>

<p><strong>Two — proof.</strong> The most specific true thing you have done. This paragraph is doing the actual work.</p>

<blockquote><p>Last year I built an attendance system for my department in Java and MySQL. Three departments now use it, and it replaced a paper register that was taking staff about 40 minutes a day. Building it taught me more about databases, and about what people actually need versus what they say they want, than any course I have taken.</p></blockquote>

<p><strong>Three — what you are learning, and how to reach you.</strong></p>

<blockquote><p>Right now I am working through system design and getting more comfortable with Spring Boot. If you are hiring backend freshers, or you are happy to answer a question about your team\'s stack, I would genuinely like to hear from you — swarnil@example.com.</p></blockquote>

<p>That is under 150 words, it is readable, and every sentence is checkable.</p>

<h2>Write in first person</h2>

<p>"Swarnil is a passionate developer who..." reads as though someone else wrote it, and nobody did. LinkedIn is a conversation, not a corporate biography. Say "I".</p>

<h2>Keywords, without stuffing</h2>

<p>Search does read this section, so the technology names should be in it — but written into sentences, not dumped at the bottom.</p>

<p><strong>Bad:</strong> Skills: Java, Python, C, C++, HTML, CSS, JavaScript, React, Node, MySQL, MongoDB, Git, Docker, AWS, Kubernetes.</p>

<p>Fifteen technologies from a fresher reads as fifteen tutorials watched. It actively reduces trust. Three or four you can genuinely be questioned about beats fifteen you cannot.</p>

<h2>The honest bit</h2>

<p>Every additional word about how motivated you are makes the profile weaker, because it displaces the one thing that is persuasive: something specific you actually did, that somebody can click on and verify.</p>

<p>If you have not built that thing yet, this is the real signal — the profile is not the problem. Go and build the small useful thing your college needs, then come back and write about it. That is a much better use of a fortnight than rewriting this paragraph nine times.</p>',
          'slug' => 'a-keyword-rich-about-section',
        ),
        2 => 
        array (
          'title' => 'Turning on Open to Work the right way',
          'duration' => 7,
          'excerpt' => 'The setting that puts you in recruiter search results — and the difference between the two versions of it that nobody explains.',
          'content' => '<p>LinkedIn has a feature specifically designed to put you in front of recruiters, it is free, and a startling number of job seekers either never switch it on or switch on the wrong version.</p>

<h2>The two versions</h2>

<p><strong>The green photo frame.</strong> Everyone sees it — your network, your current employer, everybody. Public and unmissable.</p>

<p><strong>Recruiters only.</strong> No frame. You appear in the filtered searches that recruiters using LinkedIn\'s hiring tools run. Your current employer is not shown it.</p>

<p>Which to choose:</p>

<ul>
	<li><strong>Student or unemployed:</strong> use the green frame. There is nobody to hide from, and the frame genuinely prompts people in your network to think of you.</li>
	<li><strong>Currently employed and looking quietly:</strong> recruiters-only. The filtering is imperfect and not a guarantee, but the frame is a certainty.</li>
</ul>

<h2>Fill in the details properly</h2>

<p>When you turn it on, LinkedIn asks for job titles, locations, and start availability. These are not decoration — they are the fields recruiters filter on. A blank one means you are filtered out.</p>

<ul>
	<li><strong>Job titles:</strong> add several real variants. "Software Engineer", "Backend Developer", "Java Developer", "Software Development Engineer". Different companies name the same job differently, and you want to match all of them.</li>
	<li><strong>Location:</strong> list every city you would genuinely move to, and tick remote. Restricting yourself to one city as a fresher removes most of the market.</li>
	<li><strong>Start date:</strong> immediately, unless it is untrue.</li>
	<li><strong>Job type:</strong> tick internship as well as full-time. Internships convert, and a paid internship stops the clock on your CV gap.</li>
</ul>

<h2>The part people skip</h2>

<p>Turning this on is not a strategy. It is the passive half — it means that when a recruiter searches, you can be found.</p>

<p>The active half is everything else in this platform\'s job-search module: referrals, applying directly, talking to people who work where you want to work. Realistically, referrals get more freshers hired than search does.</p>

<p>But this takes four minutes, it costs nothing, and it works while you sleep. Do it today, then go and do the active half.</p>

<h2>One more setting</h2>

<p>While you are there: set your profile URL to something readable. LinkedIn gives you <code>linkedin.com/in/swarnil-singhai-4a72b193</code> by default; you can change it to <code>linkedin.com/in/swarnilsinghai</code> in about twenty seconds.</p>

<p>Then put that on your résumé. A tidy URL on a document a human will read is a small signal that you pay attention to details — and small signals are most of what a fresher has.</p>',
          'slug' => 'turning-on-open-to-work-the-right-way',
        ),
      ),
    ),
  ),
);
