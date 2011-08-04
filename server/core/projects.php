<h1>Projects </h1> 
<h2>{Please refer the resume for updated list}</h2>
<h2>Yahoo Search, Yahoo! Inc.</h2>
<ul>
 <li>
    Search Platforms Group: Designed and developing a critical component responsible for serving, filtering and transforming data from the hadoop 
    content grid to a variety of consumers.Responsible for processing and analysis of web search data for critical decision making.
 </li>
 <li>     
      Searchmonkey Data Extraction: Designed and developed a framework for processing a variety of feed sources producing data in our proprietary 
      format, which is pushed into the yahoo search indexing module, to generate more meaningful and intelligent results. 
 </li>
</ul>   
</p>

<h2> Yahoo Search Intern, Yahoo! Inc.</h2>
<p>                                                                                                                                                                                
    Researched and identified through experimentation the performance bottlenecks in the P2P system used by Yahoo! for data distribution amongst its data centers. Improvements
    anticipated bringing 4X performance gain in the production setup. This project involved deep understanding of the algorithms underlying the bit torrent protocol. 
    Designed and implemented Spatial Alarms using mobile blueprints framework developed at Yahoo!
</p>

<h2>Context Based Author Ranker and Recommender</h2>
<p>This project was carried out in a team of two. We took care of it to deployment. The objective of the system is to provide context specific recommendation of relevant authors to another author in the system. The project was taken up keeping in mind the various knowledge sharing online communities.

The credibility and relevancy of the published articles’ authors to another reader or author needs to have a stronger foundation. We employ collaborative filtering to develop the recommendation engine for achieving this. Collaborative filtering was chosen over content based technique owing to the formers flexibility with different languages. In other terms, the project addresses the issue of providing users of a knowledge sharing system, an effective way of finding which articles and in effect which authors are of his/her concerns.

We came up with an algorithm incorporating many factors playing a part in the recommendation. The weights associated with the factors play a key role in providing a good recommendation. Making such a System learn from its experience and actually adapt so as to do better in future iterations is also a key component. The proposed system does its learning from user’s feedback and adapts itself to make better recommendations. System is evaluated on the novelty of recommendations, which means that the recommendations should be accurate and at the same time non-obvious. the Engine’s precision is evaluated against human evaluators. The second evaluation method was the “leave one out” technique. Here, we deliberately remove a ( or more ) valid recommended articles from the recommendation list of a user. We then try to observe whether they are recommended back to the user. This tests the correctness of the algorithm.

We got a correctness level of 75% and above. The dataset was constructed from the movie lens datafiles which is freely available and developed at the Univ of Minnesota.

The system finds it application in online knowledge sharing communities, library systems (recommendation of books to readers), e-commerce like online book stores.</p>

<h2> Automating Schema Matching Research</h2>
<p>The goal of this project is to develop an environment where multiple databases will be managed in a realistic application context within an organization. While the ultimate goal is to be able to propagate updates from one database to all other appropriate affected databases, this will be approached in a phase by phase manner. We have concluded that we need to consider the problem of schema integration which subsumes schema matching as an initial problem. As such, our current scope in this paper is on matching and finding correspondences between schemas so that an integrated global schema (view) can be created along with mappings to individual sources. We first present a comprehensive study of five different research models, to highlight the current state of the art in schema matching. Based on their approaches, we propose a model and a methodology to produce a global schema that leverages the advantages of the past models. Advantages of the proposed approach are outlined along with architecture and features of the proposed tool environment.</p>

<h2>
  Improved Image Search: Classification and relevance feedback</h2>
<p>
Image search has been an area of research amongst many top search engines. There is a need to provide more relevant image search results. Image processing could be considered to solve this, but only to a certain extent. The semantic aspect to an image would be better handled using the feedback given by the viewers of the image. This is where the relevancy feedback algorithm comes handy. However, the image feature vectors still need to be chosen carefully with due importance to feature weightings. This project presents a visible improved image search results on application of the relevance feedback algorithm. As per the performance tests - we presented a 85% plus precision rate.
</p>

<h2>
Auto Mail Classifier</h2>
<p>
I was working on the design part of this project, I took this up with a friend during my undergrads back home in INDIA. Later on, I was encouraged to take it up further for my class project requirements, so did that.

Well, the objective of the system, is as simple as auto classify ones incoming mails, into different categories on the basis of the content of the mails and associated metadata. So, this avoided user from manually doing the classification. This system also took care of spam prevention.

The system involved a trainer module. The account holder is required to set the initial rules for the classification. There are different base-learner modules, each taking care of a classification factor. The result of each of these learners is combined by the meta-learner. Auxillary repository contained the synonym table, the abbreviation table and the previously classification results. The learners make use of the auxillary resources at their processing stage.

The base learners are trained individually,emphasising on the factor they are supposed to take care of. The categories are defined by the user. The default ones are official, personal and spam. So, at the basic level there are filters and rules for taking care of “from”, “to”, “subject”. Regular Ex were made use of here. The initial classification done by the account owner, acts as the training phase for the base learners. The frequency of user confirmation for classification reduces once the threshold level for the number of classified mails is reached.

The feedback loop involves account holder’s overriding of the engine’s classification outcome. The mail body is processed and the different mail components are identified, “from”, “mail header”, “mail footer”, “timestamp”, “initiating IP”, “path” of the message. The summation of the products of the weight and the scaled rating for each category is done at the metalearner stage. to identify the most appropriate category.

</p>