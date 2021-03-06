import {Component, OnInit} from "@angular/core";


import {Status} from "../shared/classes/status";
import {Tweet} from "../shared/classes/tweet";

import {Profile} from "../shared/classes/profile";
import {FormBuilder, FormGroup, Validators} from "@angular/forms";

import {Like} from "../shared/classes/like";
import {AuthService} from "../shared/services/auth.service";
import {ProfileService} from "../shared/services/profile.service";
import {LikeService} from "../shared/services/like.service";
import {TweetService} from "../shared/services/tweet.service";


@Component({
	selector: "list-tweet",
	template: require("./home.list.component.html")
})

export class ListTweetsComponent implements OnInit {

	createTweetForm: FormGroup;

	tweet: Tweet = new Tweet(null, null, null, null);


	profile: Profile = new Profile(null, null, null, null, null);

	//declare needed state variables for latter use
	status: Status = null;

	tweets: Tweet[] = [];


	constructor(private authService: AuthService, private formBuilder: FormBuilder, private profileService: ProfileService, private likeService: LikeService, private tweetService: TweetService) {
	}

	//life cycling before my eyes
	ngOnInit(): void {
		this.listTweets();

		this.createTweetForm = this.formBuilder.group({
			tweetContent: ["", [Validators.maxLength(140), Validators.minLength(1), Validators.required]]
		});
	}

	getTweetProfile(): void {
		this.profileService.getProfile(this.tweet.tweetProfileId)
	}


	listTweets(): void {
		this.tweetService.getAllTweets()
			.subscribe(tweets => this.tweets = tweets);
	}

	createTweet(): void {

		let tweet = new Tweet(null, null, this.createTweetForm.value.tweetContent, null);

		this.tweetService.createTweet(tweet)
			.subscribe(status => {
				this.status = status;
				if(this.status.status === 200) {
					this.createTweetForm.reset();
					alert(this.status.message);
					this.listTweets();
				}
			});
	}
}