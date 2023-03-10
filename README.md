# Recur Future Amounts extension

This extension ensures that the RecurringContribution
record is updated whenever an attached Contribution has
is created or updated with a different amount.

It does not apply to TemplateContributions as core handles
that. However, since not all sites / payment processors
wish to update the intent of future contributions when
a contribution is added / edited that has been made optional
by moving it out of core.

When would this be relevant?
- most sites will not need or want this extension.
- generally you would want this if you are using a payment
processor that does not update the `ContributionRecur` record
when a payment comes in even though that payment signals that
the contract has changed.
