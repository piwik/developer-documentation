---
category: DevelopInDepth
---
# Pull Requests and Reviews

To [contribute code to Matomo](/guides/contributing-to-piwik-core) you will need to create a pull request. Below we explain how the Matomo core team works with pull requests.

## Process for working with PRs

This is the overall process for getting a PR merged from PR creation to merging a PR. For more details read the entire page.

* When creating a PR and you're still working on it, it is recommended to mark your PR as `Draft` (you can do this in the `Reviewers` section). Once the PR is done and ready for a review click on `Ready for review` and add the label `Needs Review`. If a PR references another issue we assign the label `not-in-changelog`. The PR author also assigns the PR to a milestone (usually the same milestone as the referenced issue).
* The reviewer selects "Approve / Reject changes / Comment" when finishing a review so it's clear if a PR is accepted or more changes are needed.
* The PR author marks an individual review conversation/comment as resolved when the feedback was applied or notices/acknowledged (not always do need changes to be made for each comment as sometimes they are suggestions etc).
  * If further feedback is needed for the conversation/comment then PR author will ask the reviewer to confirm the conversation is resolved.
* The reviewer removes the label `Needs Review` when changes are required. No new label is added for now for simplicity
* The PR author adds the label `Needs Review` again once it's ready for another review.
* When a PR is approved and no further review required then we aim to directly merge the PR if the tests pass
  *  If a PR is approved overall but tests are failing then we might need another round of review (meaning reject the PR and remove `needs review` label)
  * unless it is only needed to update the expected test results then maybe the PR author could later fix the build and merge directly without yet another review (or maybe only if the PR reviewer mentions that only test )... could clarify this further in a team meeting some day
  
## Creating a pull request

Now that your changes are published, you can send them in a pull request to the main Matomo project.

To do so, visit your fork on GitHub and select the **bugfix** branch in the branch selector (located right above the directory listing on the left side of the page). Then click the _Pull Request_ button in the middle of the top of the page.

On this screen you'll be able to see exactly what changes you've made by looking at your commits and at what files have been changed. You can use this as an opportunity to review your changes.

Read [Creating Pull requests: best practises](#creating-pull-requests-best-practises) to maximise your changes to create a great pull request.

Once you're ready to create the pull request, write a description of the pull request and any notes that are important for the person who will review your code, and then click _Send pull request_. This will create a new pull request which you will be able to find and view [here](https://github.com/matomo-org/matomo/pulls). The description should contain as much as necessary and as little as possible.

If the PR is ready for a review, assign the label `Needs Review` and put it in the correct milestone. The milestone for the PR is usually the same milestone as the original issue you worked on. If there is an issue for this PR, then we also assign the label `Not In Changelog`. This prevents the same issue being listed twice in the changelog. A PR will only be reviewed when it has the `Needs Review` label.

If the PR is not ready for a review yet and the PR is in progress, then you can click on the link `Convert to draft` which you find in the Github PR UI below `Reviewers`. Once you finished the work for the PR and it's ready for a review, you can click on `Ready for review` where it says `This pull request is still a work in progress` and assign the labels as mentioned above.

For more PR best practices read below.

And if you are a core developer, if there is an issue that the pull request will fix, and the issue is in a milestone, set the milestone of the pull request to the same one as the issue.

### Developer changelog

We keep the [developer changelog](/guides/apis#developer-changelog) up to date when any changes are made to what we [consider to be API](/guides/apis).

### Updating the pull request

Once your pull request is public, developers will review it and leave comments regarding what should be changed. You can then make changes, commit them and then push them to your remote repository (**origin**) and they will automatically be shown in the pull request.

### Sharing test example pages

If you find yourself building a simple test page to test a certain feature (like a tracking feature or a certain widget), then it may be useful to contribute this examples to our [test examples](https://github.com/matomo-org/test-examples) repository so other people can reuse such a page.

### Best practices

Here are best practises we aim to follow when creating, reviewing and merging pull requests:

* We try to avoid big pull requests and aim for small PRs that are easier to review
* Before working on a new issue it is recommended to check for pending PRs that have a `Needs Review` label
* PHP code should use our [Matomo code standards](/guides/coding-standards)
* Pull requests should contain tests

## Reviewing Pull Requests

### Reviewing Core Developer PRs

As core developers one of our primary responsibilities is to review and merge other pull requests. This document lays out the general process and things to look for.
In the pull request template on github, there is a checklist of reminders of what to look for. Here, we'll go into details:

### Functional review done

For every pull request it is expected that the reviewer will actually check out the code locally and test it. We want to make sure it does the thing it's supposed to,
and handles any error conditions gracefully.

This means manually testing and looking for possible issues in the submitted code. It's required for pull request changes to have a visible effect on the tests
(in most cases), but we don't want to rely on them alone, since it's always a possibility for people to make mistakes. The review is a chance to catch them before they become bugs.

Any problems found in the logic of a change should ideally result in new tests.

### Potential edge cases thought about

When manual testing it's also required to think about any edge cases that might occur and cause issues. Running Matomo in the cloud, we've learned that edge cases,
despite their name, do occur every now and then, and cause problems. It's far better to try and avoid these problems before we merge PRs.

Some starting points that could help when looking for edge cases:

- thinking about what happens in the code when given strange input
- thinking about whether it's possible for there to be strange internal state and what would happen if this occurred
- thinking about how this code interacts with the different Matomo subsystems

By nature, edge cases are hard to find, but it's definitely better to catch them beforehand, rather than have to debug cloud, or worse, debug a user's Matomo via email.

### Usability review done

If a feature touches Matomo's UX in any way, Thomas and Matt are required to provide their input (ping them if needed). But, we also want developers to think about usability themselves.

When reviewing a pull request, think about whether it's possible that users may be confused by how it works, or unsure of how to use it. If there's a possibility that they might reach
out to support or the forums or make a github issue, then we'd like to prevent that, either with a change to the pull request, or by creating/editing a faq so we can address these
requests quickly.

### Security review done

We also want to make sure there are no security issues introduced by this pull request. We've created a checklist here for some security issues to look out
for: [https://developer.matomo.org/guides/security-in-piwik#checklist](https://developer.matomo.org/guides/security-in-piwik#checklist), but there are many other ways vulnerabilities
can manifest. And we very much want to prevent any from getting into the codebase.

### Code review done

The code review is just that, looking for mistakes in the code, along with ways the code could be clearer, just in case the pull request author missed something. If you see something
that could be done with less code, or see something that confuses you as another developer, please bring it up.

We also find that most review items are best stated as questions, and not as demands, in order to foster a more positive environment that values collaboration over argument.

### Tests were added if useful/possible

We want to take advantage of the benefits of automated testing as much as possible. If a pull request can be tested, it should be. OR it should at least show a change in the
existing tests. This also helps to prove the feature or fix does what it's supposed to.

The level of testing would vary based on what is being reviewed, but some form of test is required, unless it is really not possible.

### Reviewed for breaking changes

If a change touches something users actively use or a piece of code that is considered public API for plugin developers or those integrating Matomo, then we want to make sure our
change doesn't break anything these users and developers might currently be doing.

This is fairly simple for code (for example, if we add a new parameter to a function considered public API we want to make sure it has a default value, so people currently calling
it won't encounter an error after updating Matomo). It's, unfortunately, a bit more complicated with users. There are many ways users manage to use Matomo, and keeping things working
the way they currently want it can be a challenge.

We want to make sure API methods still behave as they did before for the same inputs, old links still go to the same pages, CLI commands do not fail because parameters were removed,
and many other things. It can be hard to consider given there are so many ways we can break something for a user, but it's definitely important to keep our users workflows working.

### Developer changelog updated if needed

The developer changelog is located at [https://github.com/matomo-org/matomo/blob/4.x-dev/CHANGELOG.md](https://github.com/matomo-org/matomo/blob/4.x-dev/CHANGELOG.md). If a change
affects the work of plugin developers or developers who integrate Matomo into their websites, we'd want to mention the change in the developer changelog. This can include:

* breaking changes

  A change that will force them to make modifications to their plugins or integrations. As said in a previous section, we like to avoid situations like this (except for major releases),
  but sometimes it's unavoidable.

* new features, API methods, configuration options

  If a new feature is something developers may want to take advantage of, then we want to mention it so more people become aware of it.

* changes to existing features

  If we change how an existing feature or API works, we want to mention it to developers. These are not breaking changes, those are mentioned above, just changes in the how something works.
  An example would be when we introduced an allowlist for trusted hosts to download geoip databases from. It's unlikely this would break anything for existing users, but it's still worth
  mentioning.

* deprecations

  If we deprecate some code or API, we want to mention it as early as possible, so developers have ample time to stop using it before we remove it.

### Documentation added if needed

If the change is for a new feature or affects the way an existing feature works, then we'll want to modify the existing documentation (or create new documentation if it doesn't exist).
For changes that affect plugin developers and developers integrating Matomo, we'd want to document the changes within our existing phpdocs and the developer documentation website:
https://github.com/matomo-org/developer-documentation

Changes to features should be reflected in changes to user documentation. For new features we may give the task of writing documentation to technical writers or our support team,
but for smaller changes, developers might be expected to make those themselves.

We should also think about whether new faqs should be created or if we need to modify existing ones.

If a screenshot changes significantly, then we should also update screenshots on our website. If only a wording changes, then usually it's not needed to update the screenshot. There are no hard rules when or when not to update a screenshot. If basically depends if the screenshot is still clear in the context of the content. 

### Reviewing External Contributors' PRs

External contributor pull requests should be reviewed in the same way as PRs from core developers, EXCEPT:

* we should assume that they may not want to or have the time to completely fix up their pull requests. It's possible we core devs may need to fix the build,
  or apply some changes, or other minor things. We shouldn't just take over the PR unless it is an easy task or a very useful thing to have merged.

* we should assume they don't have the technical knowledge we have of Matomo and may need more help than a core dev would.

* and we should always thank them for the contribution! It's always a good sign when people take an interest in our product, and it's pretty amazing when people decide to work for free :)

### Best practices

* Read the article: [Pull Requests: How to Get and Give Good Feedback](https://www.kickstarter.com/backing-and-hacking/pull-requests-how-to-get-and-give-good-feedback).

## Merging Pull Requests

When reviewing a pull request in the current milestone, if it works, all review items have been addressed and tests pass, then core developers are allowed to merge it with a squash and merge. Small changes can be merged directly without a review if the developer is 100% certain the change won't have any side effects etc. It is still always recommended to quickly ask another developer that is online to have a look at this PR now as such PRs are quickly reviewed. Once the PR has been merged the branch can be deleted.

If a PR affects the [public API](https://github.com/matomo-org/matomo/issues/8125) in any way a PR should not be merged without a review.

For anything else, we'd have to know whether it is something we want in the current milestone. This "approval" can be an explicit comment from Thomas or Matt, or it could just be from a slack conversation (remember, we can always revert something later). Exceptions can be made, however, if the change is small and not likely to cause any problems when released. Then it's fine to change the milestone to the current one and merge it.

Since we work on issues in the current milestone first, reviewing pull requests outside of the current milestone isn't something that happens very often.
