# semistatic - a semi-static site generator for Laravel

`semistatic` is a file-backed content manager like [sitepress](https://github.com/sitepress/sitepress) but for Laravel.
It can be used for rendering content-rich pages like knowledge bases or blogs.

## When to ...
### ... not to use `semistatic`
- You want a fully-fledged CMS and content builder UI 
- You hate Markdown files
- You have no experience with PHP/Laravel
- You want something working straight out of the box
### ... use `semistatic` 
- You have a Laravel-based website with technical content
- You need to version your markdown-based content
- Your content has a hierarchy/tree-structure

## FAQ
### Can I have different content types like a FAQ and Knowledge Base in one application?
Yes. `semistatic` puts belonging content (directories) in a `shelf`. You can have a `FAQ` shelf and `Knowledge Base` shelf.
### How to mount different shelves below different routes?
Just add a new route to one of your Laravel controller actions. Then load the specific shelf in that action.

## TODOs
- [] Tree and link caching
- [] Tests
- [] Proper code documentation
- [] Proper exceptions
- [] Code samples

## Collaboration
This is a side-project and our company needs to make money. That's the cruel reality. 
That being said, this repository has not our attention. We might add new features or fix bugs on a per-need basis.
Also, the API of `semistatic` is not stable. We might introduce breaking changes in new commits.

### PR policy
Feel free to create a new PR. We might accept and merge the PR in the near future.

### Support questions
`semistatic` will have bugs and lacks features. If you need something fixed or added, you can contact us for professional development.