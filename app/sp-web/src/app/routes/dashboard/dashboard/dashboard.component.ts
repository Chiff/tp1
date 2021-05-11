import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { EventDTO } from '../../../models/sp-api';

@Component({
  selector: 'sp-dashboard',
  templateUrl: './dashboard.component.html',
  styles: [],
})
export class DashboardComponent implements OnInit {
  myEvents: EventDTO[];

  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    this.http.get<EventDTO[]>('api/event/my').subscribe((data) => {
      this.myEvents = data;
    });
  }
}
