# Set it up

* Get datasets from
    * http://ai.stanford.edu/~amaas/data/sentiment/
    * http://www.sananalytics.com/lab/twitter-sentiment/
* Run `php app.php`
* Follow commands in MySQL given at the end of `app.php`

# What is it?

In my 6th Semeseter, the subject **Theory and Foundation of Computaion and Automata** introduced me to Conditional probability. I was excited to learn how I could use this to build something nice, and came across classifiers.

Lets talk Naive Bayes... a really naive implementation of a classifier.

Applying conditional probability to ascertain the sentiment of a sentence is easy to achieve.
The formula goes as follows:

```
Let:
    WifS    = Probability of word for a sentiment
    S       = Probability of a sentiment
    X       = WifS * S
    WifnotS = Probability of word for all sentiments except the one you're looking for
    notS    = Probability of all sentiments except the one you're looking for

Then,
    probability of a sentiment if word = X / ( X + WifnotS * notS)
```

Next, all we do is multiply the probability we get above for each word and get the probability of a sentiment for a sentence. (Be wary of underflow)

# How do I train this?

Look at the beginning of this document for locations of datasets.

```
WifS = unique words occuring in in the sentiment and how many times it occurs / total number of words in the sentiment
S    = total number of words in the sentiment / total number of words
```

This should help you figure out the rest.

_And why is this so naive?_

That's because we don't look for relations between the words. Two words together can have a different sentiment than if we just consider one word by itself.
A simple example: `This is not good.`

I was kind of satisfied with what I had, but there were many sentences that were classified wrong! And they were because of negations in the sentences. For example: `I don't give up` or `The best of friends won't betray you and won't give up on you`.

Then `/me` came up with a genius idea...

# The first attempt

These kind of sentences were formed with words which negate an action like: don't (do not), won't (will not), shouldn't (should not), etc. Or words which shift the sentiment of the sentence like: but, nevertheless, yet, etc.
Each occurrence of the word reverses the overall sentiment of the sentence. Then all I have to do is swap the probabilities accordingly, right?

So I decided to try this approach:
If there are odd number of such verb negations, I swap the probabilities. If not, I keep it as it is.

The results were interesting.

This was before:

    I don't give up.
    Array
    (
        [pos] => 0.38048559473042
        [neg] => 0.61951440526958
    )

    The best of friends won't betray you.
    Array
    (
        [pos] => 0.80352129453774
        [neg] => 0.19647870546226
    )


This is after:

    I don't give up.
    Array
    (
        [pos] => 0.61951440526958
        [neg] => 0.38048559473042
    )

    The best of friends won't betray you.
    Array
    (
        [pos] => 0.19647870546226
        [neg] => 0.80352129453774
    )


The first sentence worked great, but the second one was thrown off completely. This was because most of the words in the sentence had more positive sentiment individually (naivety of this algorithm).

# Working the kinks

How about I split the sentences with transition words and further split the splits with action negation words and calculate the probability of each split in the usual fashion. Now when multiplying the probabilities, I reverse the sentiments of every `n+1 th` split occurring after the shift word and reverse the sentiment of splits which show up after every action negation words, while dropping the negation words.

           +-----------------+
           | I don't give up |
           +-------+---------+
                   |
      +------------+--------------+
      |                           |
      |                           |
    +-v-+                    +----v----+
    | I |                    | give up |
    +---+                    +---------+

I get this:

    I don't give up.
    Array
    (
        [pos] => 0.51057454977743
        [neg] => 0.48942545022257
    )

    The best of friends won't betray you.
    Array
    (
        [pos] => 0.62281730915385
        [neg] => 0.37718269084615
    )

# Conclusion

Although, in the process of dropping the negation words, we lose some of the context for the bayes algorithm. In my case, with the modified method, the sentence `Life is not good` gave a positive sentiment probability as `0.62463533949116` and negative as `0.37536466050884`. Upon further inspection, I saw that the positive occurrences of `good` was almost the same as the negative occurrences of `good`, so it didn't matter at all. The word `life` had a much larger positive probability and that was outweighing everything else. With the word `don't` included, we get a correct negative probability.

It was a fun experiment trying to tune this algorithm. This was my first experience with machine learning, and it has me hooked ever since.