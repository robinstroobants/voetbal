# voetbal

# Na prod deploy, éénmalig taggen:
git checkout main && git merge beta
./scripts/deploy-prod.sh
git tag v3.0.0
git push origin v3.0.0

