-- ============================================================
--  Seed data: subjects, topics, validated question bank,
--  default accounts and exam configuration.
--
--  Default accounts (change after first login):
--    Admin     : ADMIN001  / Admin@123
--    Instructor: STAFF018  / Staff@123   (Inst. Tilahun Kebede)
--    Students  : SMU/CS/1234, SMU/CS/1207, SMU/CS/1211, SMU/CS/1219
--                password Student@123
-- ============================================================

USE smu_exit_exam;

-- ---------- subjects ----------
INSERT INTO subjects (id, name, description) VALUES
 (1,'Data Structures & Algorithms','Arrays, linked lists, stacks, queues, trees, graphs, sorting and searching.'),
 (2,'Database Systems','ER modeling, relational model, SQL, normalization and transactions.'),
 (3,'Software Engineering','Process models, requirements engineering, design and testing.'),
 (4,'Operating Systems','Processes, scheduling, memory management and file systems.'),
 (5,'Computer Networks','OSI/TCP-IP layers, addressing, routing, transport and security.');

-- ---------- topics ----------
INSERT INTO topics (id, subject_id, name) VALUES
 (1, 1,'Arrays & Linked Lists'),
 (2, 1,'Stacks & Queues'),
 (3, 1,'Trees & Graphs'),
 (4, 1,'Sorting & Searching'),
 (5, 2,'ER Modeling & Relational Model'),
 (6, 2,'SQL Queries'),
 (7, 2,'Normalization'),
 (8, 2,'Transactions & Concurrency'),
 (9, 3,'SDLC & Agile'),
 (10,3,'Requirements Engineering'),
 (11,3,'UML & Design Principles'),
 (12,3,'Software Testing'),
 (13,4,'Processes & Threads'),
 (14,4,'CPU Scheduling'),
 (15,4,'Memory Management'),
 (16,4,'File Systems'),
 (17,5,'OSI & TCP/IP Layers'),
 (18,5,'IP Addressing & Subnetting'),
 (19,5,'Routing & Transport'),
 (20,5,'Network Security');

-- ---------- users ----------
INSERT INTO users (name, id_number, password_hash, role, status) VALUES
 ('System Admin'      ,'ADMIN001'   ,'$2y$10$WAoSgLjeckQxeA9.oru96.iVR3Di0aFsZ9gitJYv2Ypg2c9a99fjK','admin','active'),
 ('Tilahun Kebede'    ,'STAFF018'   ,'$2y$10$V30R7DvOQDvOhdtZBpl01.qgqWe2mgrezS5QSCZekAblHI.by.GHK','instructor','active'),
 ('Kaleb Aklilu'      ,'SMU/CS/1234','$2y$10$IFuyIjq3UOaokgCXiKn7teoe7rFyWDIjVRa6UciYPm10Hd1zJWsja','student','active'),
 ('Edel Wondimeneh'   ,'SMU/CS/1207','$2y$10$IFuyIjq3UOaokgCXiKn7teoe7rFyWDIjVRa6UciYPm10Hd1zJWsja','student','active'),
 ('Fitsum Abrham'     ,'SMU/CS/1211','$2y$10$IFuyIjq3UOaokgCXiKn7teoe7rFyWDIjVRa6UciYPm10Hd1zJWsja','student','active'),
 ('Solomon Dawit'     ,'SMU/CS/1219','$2y$10$IFuyIjq3UOaokgCXiKn7teoe7rFyWDIjVRa6UciYPm10Hd1zJWsja','student','active');

-- ---------- exam configuration ----------
INSERT INTO settings (setting_key, setting_value) VALUES
 ('mock_question_count'    ,'50'),
 ('mock_time_limit_minutes','60'),
 ('practice_question_count','10'),
 ('session_timeout_minutes','30'),
 ('readiness_target'       ,'80');

-- ============================================================
--  QUESTION BANK  (5 subjects x 4 topics x 5 questions = 100)
--  All validated (is_validated = 1); difficulty is spread.
-- ============================================================

-- ---------- Topic 1: Arrays & Linked Lists ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (1,'What is the time complexity of accessing the i-th element of an array?','O(1)','O(log n)','O(n)','O(n log n)','A','Arrays use index arithmetic, so any element is reached in constant time O(1).','easy',1),
 (1,'Inserting a node at the head of a singly linked list takes:','O(n)','O(1)','O(log n)','O(n^2)','B','Only the head pointer and the new node''s next link change, so insertion at the head is O(1).','easy',1),
 (1,'Which operation is NOT efficient on a singly linked list?','Inserting after a given node','Deleting the head node','Random access by index','Inserting at the tail with a tail pointer','C','Linked lists are sequential structures; reaching index i requires walking i nodes, i.e. O(n).','easy',1),
 (1,'A dynamic array doubles its capacity when full. What is the amortized cost of one append?','O(1) amortized','O(n) always','O(log n) amortized','O(n^2) amortized','A','Although a single grow costs O(n), doubling makes the average cost of each append constant.','medium',1),
 (1,'Reversing a singly linked list iteratively requires:','O(n) time and O(1) extra space','O(n) time and O(n) extra space','O(n^2) time and O(1) space','O(log n) time and O(1) space','A','Each node is relinked once with three pointers, so linear time and constant space suffice.','medium',1);

-- ---------- Topic 2: Stacks & Queues ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (2,'Which data structure processes elements in First-In-First-Out (FIFO) order?','Stack','Queue','Binary tree','Hash table','B','A queue dequeues from the front and enqueues at the rear, giving FIFO behavior.','easy',1),
 (2,'Which application is a natural fit for a stack?','Printer job scheduling','Undo operation in an editor','Breadth-first search','Round-robin scheduling','B','Undo needs the most recent action first, which is LIFO - exactly a stack.','easy',1),
 (2,'In a circular queue of capacity n, the queue is full when:','rear == front','(rear + 1) mod n == front','front == 0 and rear == n-1','rear - front == n','B','One slot is sacrificed to distinguish full from empty, so (rear+1) mod n == front means full.','medium',1),
 (2,'Evaluating the postfix expression "6 2 3 + -" gives:','1','7','5','11','A','2+3=5, then 6-5=1. Postfix is evaluated with a stack in one pass.','medium',1),
 (2,'Implementing a queue with two stacks (in-stack and out-stack) gives amortized dequeue cost of:','O(n) per dequeue','O(1) amortized','O(log n) amortized','O(n^2) amortized','B','Each element is moved at most once between the stacks, so the amortized cost is constant.','hard',1);

-- ---------- Topic 3: Trees & Graphs ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (3,'The maximum number of nodes in a binary tree of height h (root at height 0) is:','2^h','2^(h+1) - 1','2h + 1','h^2','B','Each level i can hold 2^i nodes; summing 0..h gives 2^(h+1) - 1.','easy',1),
 (3,'Which traversal of a Binary Search Tree visits keys in ascending order?','Preorder','Postorder','Inorder','Level-order','C','Inorder traversal (left, root, right) of a BST yields sorted output.','easy',1),
 (3,'Breadth-first search of a graph uses which auxiliary structure?','Stack','Queue','Priority queue','Hash table','B','BFS explores level by level, which requires a FIFO queue of discovered nodes.','easy',1),
 (3,'Dijkstra''s algorithm does NOT produce correct shortest paths when the graph contains:','Undirected edges','Negative edge weights','Connected components','More than 100 nodes','B','Dijkstra greedily finalizes nodes, an assumption broken by negative weights (use Bellman-Ford).','medium',1),
 (3,'An AVL tree is a BST where for every node the heights of its two child subtrees differ by at most:','0','1','2','log n','B','The AVL balance condition allows a height difference of at most 1, guaranteeing O(log n) height.','hard',1);

-- ---------- Topic 4: Sorting & Searching ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (4,'The best-case time complexity of QuickSort is:','O(n^2)','O(n log n)','O(n)','O(log n)','B','Balanced partitions give the recurrence T(n)=2T(n/2)+O(n), which solves to O(n log n).','easy',1),
 (4,'Which sorting algorithm is stable and guarantees O(n log n) in the worst case?','QuickSort','Merge Sort','Heap Sort','Selection Sort','B','Merge Sort preserves the relative order of equal keys and always splits in half.','medium',1),
 (4,'Binary search on a sorted array of n elements requires at most approximately:','n/2 comparisons','log2(n) comparisons','n comparisons','n log n comparisons','B','Each comparison halves the search interval, giving at most about log2(n)+1 comparisons.','easy',1),
 (4,'Which algorithm finds the k-th smallest element in expected linear time?','Merge Sort','QuickSelect','Heap Sort','Insertion Sort','B','QuickSelect recurses into one partition only, giving O(n) expected time.','hard',1),
 (4,'For nearly sorted data, which simple algorithm performs close to O(n)?','Selection Sort','Insertion Sort','Heap Sort','QuickSort','B','Insertion Sort shifts each element only a small distance when the array is nearly sorted.','medium',1);

-- ---------- Topic 5: ER Modeling & Relational Model ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (5,'In an ER diagram, a many-to-many relationship between Student and Course is usually resolved by:','Merging the two entities','Adding an associative (junction) entity','Storing a list of course IDs in Student','Adding a foreign key in Course only','B','M:N is decomposed into two 1:N relationships through a junction table such as Enrollment.','easy',1),
 (5,'A foreign key is a column whose values must match:','The primary key of another table','Any unique index','A check constraint','The table''s own primary key','A','Referential integrity requires FK values to reference existing primary key values.','easy',1),
 (5,'Which attribute type must be represented by a separate entity/table when converted from ER?','Simple attribute','Multivalued attribute','Derived attribute','Key attribute','B','Multivalued attributes violate 1NF, so they become their own table with an FK.','medium',1),
 (5,'An instance of an entity is called a:','Tuple in the corresponding relation','Attribute','Domain','Cardinality','A','Each entity instance maps to one row (tuple) of the relational table.','easy',1),
 (5,'A weak entity is one that:','Has a primary key of its own','Cannot be identified by its own attributes alone','Is never stored in the database','Has no attributes','B','A weak entity depends on an identifying (owner) entity plus a partial key.','medium',1);

-- ---------- Topic 6: SQL Queries ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (6,'Which SQL clause filters groups after aggregation?','WHERE','HAVING','ORDER BY','GROUP BY','B','WHERE filters rows before grouping; HAVING filters the aggregated groups.','easy',1),
 (6,'SELECT COUNT(*) FROM students WHERE dept=''CS''; returns:','The number of CS students','The names of CS students','An error: COUNT needs a column','The average of the dept column','A','COUNT(*) counts all rows that satisfy the WHERE condition.','easy',1),
 (6,'An INNER JOIN between Orders and Customers returns:','All orders and all customers','Only rows with matching keys in both tables','All orders plus unmatched customers','Cartesian product of both tables','B','INNER JOIN keeps only rows where the join condition matches in both sides.','easy',1),
 (6,'Which query lists students who scored above the overall average?','SELECT name FROM results WHERE score > AVG(score);','SELECT name FROM results WHERE score > (SELECT AVG(score) FROM results);','SELECT name FROM results HAVING score > AVG(score);','SELECT name FROM results WHERE score > MAX(score);','B','Aggregate comparisons need a scalar subquery; the AVG must be computed separately.','medium',1),
 (6,'What does SELECT DISTINCT dept FROM students; do?','Deletes duplicate rows from the table','Returns unique department values','Groups by name','Counts each department once','B','DISTINCT removes duplicate values from the result set, not from the table.','easy',1);

-- ---------- Topic 7: Normalization ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (7,'A relation is in 2NF if it is in 1NF and:','Every determinant is a candidate key','No non-key attribute depends on part of a candidate key','Transitive dependencies are removed','Multivalued dependencies are removed','B','2NF removes partial dependencies of non-key attributes on part of a composite key.','easy',1),
 (7,'A relation is in 3NF if it is in 2NF and every non-key attribute is:','Multivalued','Transitively dependent on the primary key','Non-transitively dependent on the primary key','A candidate key','C','3NF removes transitive dependencies of non-key attributes on the key.','easy',1),
 (7,'BCNF requires that for every non-trivial functional dependency X -> Y:','X is a foreign key','X is a superkey','Y is a superkey','Y contains only prime attributes','B','BCNF upgrades 3NF: every determinant must be a superkey.','medium',1),
 (7,'The main goal of normalization is to:','Speed up every query','Remove redundancy and update anomalies','Encrypt sensitive data','Increase storage usage','B','Normalization reduces redundancy and prevents insert/update/delete anomalies.','easy',1),
 (7,'A table with columns (StudentID, Course, Advisor) where Advisor depends only on StudentID suffers from:','Partial dependency (1NF only)','Transitive dependency','Multivalued dependency','Join dependency','B','Advisor depends on StudentID which is only part of the key (StudentID, Course) - a partial dependency.','hard',1);

-- ---------- Topic 8: Transactions & Concurrency ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (8,'The ACID properties of a transaction stand for:','Asynchronous, Consistent, Isolated, Durable','Atomicity, Consistency, Isolation, Durability','Atomicity, Concurrency, Integrity, Dependability','Access, Control, Isolation, Data','B','ACID = Atomicity, Consistency, Isolation, Durability.','easy',1),
 (8,'Which isolation level prevents dirty reads but can still cause non-repeatable reads?','READ UNCOMMITTED','READ COMMITTED','SERIALIZABLE','None of the above','B','READ COMMITTED only reads committed data, yet re-reads may differ if others commit in between.','medium',1),
 (8,'A deadlock between two transactions is typically resolved by:','Waiting forever','The DBMS aborting (rolling back) one victim transaction','Restarting the database','Committing both transactions anyway','B','The DBMS detects a cycle in the wait-for graph and aborts one transaction to break it.','medium',1),
 (8,'If transaction T1 reads a row, T2 updates and commits it, and T1 reads it again with a different value, the anomaly is:','Dirty read','Phantom read','Non-repeatable read','Lost update','C','Re-reading modified committed data within one transaction is a non-repeatable read.','hard',1),
 (8,'The WAL (write-ahead log) technique guarantees:','Faster SELECT queries','Durability and recoverability after a crash','Automatic encryption','Data compression','B','Logging changes before applying them allows the DBMS to redo/undo work after a crash.','hard',1);

-- ---------- Topic 9: SDLC & Agile ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (9,'In the Waterfall model, the phase that follows Requirements Analysis is:','Design','Testing','Deployment','Maintenance','A','Waterfall is strictly sequential: requirements, design, implementation, testing, deployment, maintenance.','easy',1),
 (9,'A core value of the Agile Manifesto is:','Comprehensive documentation over working software','Individuals and interactions over processes and tools','Contract negotiation over customer collaboration','Following a plan over responding to change','B','The manifesto values individuals and interactions, working software, customer collaboration and responding to change.','easy',1),
 (9,'In Scrum, the Sprint is:','A two-week to one-month development iteration','The final testing phase','A bug report','The product backlog','A','A sprint is a fixed-length iteration (typically 2-4 weeks) producing a potentially shippable increment.','easy',1),
 (9,'Which model is most appropriate when requirements are unclear and user feedback is essential?','Waterfall','V-Model','Prototyping / Evolutionary model','Big-bang model','C','Prototyping builds evolving versions to elicit real requirements from users.','medium',1),
 (9,'The main purpose of the Daily Stand-up in Scrum is to:','Report to management','Synchronize the team and surface impediments','Assign tasks for the month','Write the sprint review','B','The daily scrum is a short team synchronization meeting focused on progress and blockers.','medium',1);

-- ---------- Topic 10: Requirements Engineering ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (10,'A functional requirement describes:','Performance targets','What the system must do','Security policies','Hardware specifications','B','Functional requirements specify system behavior/services; qualities like performance are non-functional.','easy',1),
 (10,'"The system shall respond within 2 seconds" is an example of:','A functional requirement','A non-functional requirement','A use case','A test case','B','Response time is a quality attribute - a non-functional requirement.','easy',1),
 (10,'Which technique is best for resolving conflicting requirements among many stakeholders?','Interviewing one user only','Prioritization and negotiated trade-offs','Ignoring minority requirements','Random selection','B','Elicitation includes negotiation: stakeholders prioritize and agree on trade-offs.','medium',1),
 (10,'A well-written requirement should be:','Ambiguous but short','Testable, unambiguous and traceable','Written in code','Hidden from users','B','Quality requirements are verifiable, clear and traceable to design and tests.','medium',1),
 (10,'Requirements validation primarily checks that the requirements:','Match the design pattern','Are consistent, complete and realistic','Compile successfully','Use the newest technology','B','Validation confirms the documented requirements reflect what stakeholders actually need.','hard',1);

-- ---------- Topic 11: UML & Design Principles ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (11,'In a UML class diagram, a diamond at one end of an association denotes:','Inheritance','Aggregation or composition','Dependency','Realization','B','The diamond marks whole-part relationships: hollow = aggregation, filled = composition.','easy',1),
 (11,'A UML use case diagram shows:','Classes and attributes','Actors and the functions they perform','Database tables','Network topology','B','Use case diagrams relate external actors to the system functions (use cases) they trigger.','easy',1),
 (11,'The Open/Closed Principle states that a module should be:','Open for modification, closed for extension','Open for extension, closed for modification','Always static','Written in one file','B','OCP: extend behavior by adding code, not by changing tested code.','medium',1),
 (11,'A sequence diagram models interaction ordered by:','Class hierarchy','Time from top to bottom','Package dependencies','Data flow direction','B','Lifelines exchange messages top-down in time order.','medium',1),
 (11,'Dependency Injection is used mainly to:','Increase coupling between classes','Achieve loose coupling and testability','Encrypt dependencies','Speed up compilation','B','Injecting dependencies (rather than hard-coding them) decouples components and eases unit testing.','hard',1);

-- ---------- Topic 12: Software Testing ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (12,'Unit testing focuses on:','The whole system in production','Individual components in isolation','User acceptance','Database backups','B','Unit tests verify the smallest testable parts (functions/classes) independently.','easy',1),
 (12,'Testing an application end-to-end as a black box against requirements is called:','Unit testing','System testing','Regression testing','Static analysis','B','System testing exercises the integrated system against its requirements.','easy',1),
 (12,'A test that verifies previously working features still work after a change is:','Smoke test','Regression test','Alpha test','Stress test','B','Regression suites re-run existing tests to catch breakage introduced by new changes.','easy',1),
 (12,'White-box testing designs test cases using:','Requirements only','Knowledge of the internal code structure','Random inputs','User manuals','B','White-box (structural) testing derives cases from control flow, branches and paths.','medium',1),
 (12,'In the V-Model, system acceptance testing corresponds to which development activity?','Coding','Requirements specification','Unit design','Deployment','B','The V-Model maps each test level to its matching specification phase; acceptance maps to requirements.','hard',1);

-- ---------- Topic 13: Processes & Threads ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (13,'Which state describes a process that is ready but waiting for CPU?','Running','Ready','Blocked','Terminated','B','A ready process has everything it needs except the CPU itself.','easy',1),
 (13,'Threads within the same process share:','Stack and registers','Heap memory and open files','Nothing','Only the program counter','B','Threads have private stacks/registers but share the process heap, code and open files.','easy',1),
 (13,'A system call that creates a new process in UNIX is:','fork()','exec()','wait()','kill()','A','fork() duplicates the calling process; exec() then replaces the image.','easy',1),
 (13,'A race condition occurs when:','Two processes never interact','The result depends on the timing of concurrent accesses','A process exceeds its memory','The CPU overheats','B','Unsynchronized concurrent access makes outcomes timing-dependent.','medium',1),
 (13,'A mutex differs from a binary semaphore because:','It is faster','Only its owner can release it','It has no blocking','It only works on files','B','Mutexes enforce ownership (lock and unlock by the same thread); semaphores have no ownership.','hard',1);

-- ---------- Topic 14: CPU Scheduling ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (14,'Which scheduling algorithm can cause starvation of long jobs?','FCFS','Shortest Job First','Round Robin','Lottery','B','SJF always prefers short jobs, so long jobs may wait indefinitely.','easy',1),
 (14,'Round Robin scheduling improves fairness primarily through:','Priority queues','Time slicing (quantum)','Longest-job-first','Batch processing','B','Each ready process gets the CPU for one quantum in turn.','easy',1),
 (14,'If the time quantum in Round Robin is made very large, RR degenerates into:','SJF','FCFS','Priority scheduling','MLFQ','B','With a quantum larger than every burst, each job runs to completion in arrival order - FCFS.','medium',1),
 (14,'Turnaround time of a process is:','CPU burst only','Waiting time only','Completion time minus arrival time','Response time of the first request','C','Turnaround = finish - arrival (total time in system).','medium',1),
 (14,'Priority aging is a technique that:','Increases priority of long-waiting processes to prevent starvation','Deletes old processes','Speeds up the clock','Reduces context switches','A','Aging gradually raises the priority of waiting processes so they eventually run.','hard',1);

-- ---------- Topic 15: Memory Management ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (15,'Internal fragmentation is associated with:','Paging','Fixed-size (contiguous) allocation','Pure segmentation','Virtual memory','B','Allocating fixed blocks larger than needed wastes space inside the block.','easy',1),
 (15,'A page fault occurs when:','A page is found in RAM','The accessed page is not in physical memory','The CPU overheats','A file is deleted','B','The MMU raises a fault; the OS loads the page from disk and retries.','easy',1),
 (15,'The TLB is used to:','Cache page-table translations','Store disk blocks','Manage processes','Encrypt memory','B','The TLB caches recent virtual-to-physical translations to avoid page-table walks.','medium',1),
 (15,'Thrashing happens when:','The CPU is underutilized because processes are constantly paging','RAM is fully idle','Disk is empty','There are too few users','A','If the working sets exceed memory, pages churn between disk and RAM and CPU utilization collapses.','medium',1),
 (15,'LRU page replacement evicts the page that:','Was loaded first','Has not been used for the longest time','Is smallest','Has the highest address','B','LRU approximates optimal replacement using recency of use.','hard',1);

-- ---------- Topic 16: File Systems ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (16,'A directory in most file systems is essentially:','A file mapping names to file identifiers','A special CPU register','A section of RAM','A device driver','A','Directories store entries that map file names to inodes/FAT entries.','easy',1),
 (16,'An inode stores:','The file name only','File metadata and block pointers','Directory path only','User passwords','B','The inode holds size, permissions, timestamps and pointers to data blocks (not the name).','easy',1),
 (16,'Contiguous file allocation suffers mainly from:','Large index tables','External fragmentation','Slow random access within the file','No sequential access','B','Free space becomes fragmented into holes too small for new files.','medium',1),
 (16,'Journaling in a modern file system provides:','Faster reads','Crash consistency (metadata/log recovery)','Compression','Encryption','B','The journal records pending changes so the FS can recover to a consistent state after a crash.','medium',1),
 (16,'A hard link differs from a copy because a hard link:','Points to the same inode as the original','Duplicates the data blocks','Works across file systems always','Cannot be deleted','A','Hard links are additional directory entries referencing the same inode and data.','hard',1);

-- ---------- Topic 17: OSI & TCP/IP Layers ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (17,'Which OSI layer is responsible for end-to-end reliable delivery?','Physical','Network','Transport','Session','C','The transport layer (TCP) provides end-to-end delivery, ordering and reliability.','easy',1),
 (17,'Data-link layer units of data are called:','Packets','Frames','Segments','Bits','B','Layer 2 groups bits into frames; layer 3 uses packets, layer 4 segments.','easy',1),
 (17,'At which OSI layer does IP operate?','Layer 2','Layer 3','Layer 4','Layer 7','B','IP provides logical addressing and routing at the network layer.','easy',1),
 (17,'Which protocol resolves an IP address into a MAC address?','ARP','DNS','DHCP','ICMP','A','ARP broadcasts a request mapping IPv4 address to the link-layer MAC.','medium',1),
 (17,'Encapsulation means:','Each layer wraps the upper layer''s data with its own header','Compressing packets','Encrypting all traffic','Combining two networks','A','Going down the stack, each protocol adds its header around the payload from above.','medium',1);

-- ---------- Topic 18: IP Addressing & Subnetting ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (18,'How many usable host addresses are in the subnet 192.168.1.0/24?','256','254','128','62','B','2^32-24 = 256 addresses minus network and broadcast = 254 usable hosts.','easy',1),
 (18,'The subnet mask 255.255.255.240 corresponds to CIDR prefix:','/26','/27','/28','/29','C','240 leaves 4 host bits: /28 (2^4=16 addresses per subnet).','medium',1),
 (18,'Which address is a private (non-routable) IPv4 address?','172.32.10.1','192.168.10.5','8.8.8.8','203.0.113.7','B','RFC1918 private ranges are 10.0.0.0/8, 172.16.0.0/12 and 192.168.0.0/16.','easy',1),
 (18,'DHCP is used to:','Assign IP configuration automatically to hosts','Resolve domain names','Route packets between networks','Encrypt web traffic','A','DHCP hands out IP address, mask, gateway and DNS lease to clients.','easy',1),
 (18,'A network has 192.168.4.0/24 and needs 8 equal subnets. Each subnet will have:','32 addresses','16 addresses','64 addresses','8 addresses','A','8 subnets need 3 borrowed bits: /27, giving 2^5=32 addresses (30 usable) each.','hard',1);

-- ---------- Topic 19: Routing & Transport ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (19,'Which protocol is connectionless and does not guarantee delivery?','TCP','UDP','FTP','SSH','B','UDP sends datagrams without handshakes, ordering or retransmission.','easy',1),
 (19,'The TCP three-way handshake sequence is:','SYN, ACK, FIN','SYN, SYN-ACK, ACK','ACK, SYN, NAK','SYN, DATA, FIN','B','TCP connections start with SYN, SYN-ACK, then ACK.','easy',1),
 (19,'RIP differs from OSPF mainly because RIP:','Is a link-state protocol','Uses hop count as its metric','Runs only on routers over 10kg','Is a transport protocol','B','RIP is distance-vector with hop-count metric; OSPF is link-state with cost metric.','medium',1),
 (19,'Port number 443 is the default for:','HTTP','HTTPS over TLS','DNS','SMTP','B','HTTPS uses TCP port 443; HTTP uses 80, DNS 53, SMTP 25.','easy',1),
 (19,'NAT (Network Address Translation) primarily:','Maps private IP addresses to public ones','Prevents all attacks','Increases bandwidth','Compresses headers','A','NAT rewrites addresses so many private hosts share few public IPs.','medium',1);

-- ---------- Topic 20: Network Security ----------
INSERT INTO questions (topic_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, difficulty, is_validated) VALUES
 (20,'A firewall primarily filters traffic based on:','Rules about addresses, ports and protocols','File content only','CPU load','Screen brightness','A','Firewalls apply allow/deny rules to network traffic attributes.','easy',1),
 (20,'HTTPS secures HTTP traffic using:','TLS encryption and certificates','Zip compression','MAC filtering','A longer cable','A','TLS provides confidentiality, integrity and server authentication for HTTPS.','easy',1),
 (20,'Symmetric encryption differs from asymmetric because symmetric uses:','Two different keys','The same key for encryption and decryption','No key at all','Only hashes','B','AES (symmetric) shares one secret key; RSA (asymmetric) uses key pairs.','easy',1),
 (20,'A SQL injection attack succeeds mainly when the application:','Uses prepared statements','Concatenates untrusted input into queries','Encrypts passwords','Uses HTTPS','B','Unvalidated input concatenated into SQL lets attackers change query semantics; parameterized queries prevent it.','medium',1),
 (20,'A hash function used for storing passwords should be:','Fast and unkeyed like MD5','Slow and salted, e.g. bcrypt/Argon2','Reversible','Replaced by encryption','B','Password hashes must be slow and salted to resist offline brute-force attacks.','hard',1);

-- ---------- sanity check ----------
-- Expected: 100 questions across 20 topics, all validated.
